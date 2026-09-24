<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\NouvelleCommandeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CommandeController extends Controller
{
    /**
     * Nombre de commandes affichées par page dans le tableau du marchand.
     */
    private const PAR_PAGE = 5;

    /**
     * Réception d'un panier (une ou plusieurs lignes) depuis la vitrine
     * publique. Aucune connexion requise.
     */
    public function store(Request $request, string $identifiant): JsonResponse
    {
        $marchand = User::where('pseudo', $identifiant)->first()
            ?? User::where('slug', $identifiant)->first();

        if (! $marchand && ctype_digit($identifiant)) {
            $marchand = User::find((int) $identifiant);
        }

        if (! $marchand) {
            return response()->json(['message' => 'Boutique introuvable.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom_client' => ['required', 'string', 'max:255'],
            'telephone_client' => ['required', 'string', 'max:30'],
            'mode' => ['required', 'in:recuperer,livrer'],
            'date_recuperation' => ['required_if:mode,recuperer', 'nullable', 'date', 'after_or_equal:today'],
            'heure_recuperation' => ['required_if:mode,recuperer', 'nullable', 'string', 'max:10'],
            'adresse_livraison' => ['required_if:mode,livrer', 'nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produit_id' => ['required', 'integer'],
            'items.*.quantite' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'nom_client.required' => 'Merci d\'indiquer votre nom.',
            'telephone_client.required' => 'Merci d\'indiquer votre numéro de téléphone.',
            'date_recuperation.required_if' => 'Merci d\'indiquer une date de récupération.',
            'heure_recuperation.required_if' => 'Merci d\'indiquer une heure de récupération.',
            'adresse_livraison.required_if' => 'Merci d\'indiquer une adresse de livraison.',
            'items.required' => 'Votre panier est vide.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $donnees = $validator->validated();

        $produits = Produit::where('user_id', $marchand->id)
            ->whereIn('id', collect($donnees['items'])->pluck('produit_id'))
            ->get()
            ->keyBy('id');

        if ($produits->isEmpty()) {
            return response()->json(['message' => 'Ces produits ne sont plus disponibles.'], 422);
        }

        $commande = DB::transaction(function () use ($donnees, $marchand, $produits) {
            $sousTotal = 0;
            $prixLivraisonMax = 0;
            $lignesAPreparer = [];

            foreach ($donnees['items'] as $item) {
                $produit = $produits->get($item['produit_id']);
                if (! $produit) {
                    continue;
                }

                // Le prix vient toujours de la base (prix après promo éventuelle),
                // jamais du navigateur du client.
                $prixUnitaire = $produit->prixFinal();
                $sousTotalLigne = $prixUnitaire * $item['quantite'];
                $sousTotal += $sousTotalLigne;

                if ($donnees['mode'] === 'livrer' && $produit->aLivraison()) {
                    $prixLivraisonMax = max($prixLivraisonMax, $produit->prix_livraison);
                }

                $lignesAPreparer[] = [
                    'produit_id' => $produit->id,
                    'nom_produit' => $produit->nom,
                    'prix_unitaire' => $prixUnitaire,
                    'prix_initial' => $produit->aRemise() ? $produit->prix : null,
                    'quantite' => $item['quantite'],
                    'sous_total' => $sousTotalLigne,
                ];
            }

            $prixLivraison = $donnees['mode'] === 'livrer' ? $prixLivraisonMax : 0;

            $commande = Commande::create([
                'user_id' => $marchand->id,
                'source' => Commande::SOURCE_EN_LIGNE,
                'nom_client' => $donnees['nom_client'],
                'telephone_client' => $donnees['telephone_client'],
                'mode' => $donnees['mode'],
                'date_recuperation' => $donnees['date_recuperation'] ?? null,
                'heure_recuperation' => $donnees['heure_recuperation'] ?? null,
                'adresse_livraison' => $donnees['adresse_livraison'] ?? null,
                'sous_total' => $sousTotal,
                'prix_livraison' => $prixLivraison ?: null,
                'total' => $sousTotal + $prixLivraison,
            ]);

            foreach ($lignesAPreparer as $ligne) {
                $commande->lignes()->create($ligne);
            }

            return $commande;
        });

        $this->notifierMarchand($commande);

        return response()->json([
            'message' => 'Commande envoyée avec succès.',
            'numero' => $commande->numero,
            'recuUrl' => route('commandes.recu', $commande->numero),
        ]);
    }

    /**
     * Liste paginée des commandes du marchand connecté, filtrable par origine :
     * ?source=en_ligne (vitrine) ou ?source=boutique (ventes en magasin).
     */
    public function index(Request $request): View
    {
        $base = Auth::user()->commandes();

        $compteurs = [
            'toutes' => (clone $base)->count(),
            'en_ligne' => (clone $base)->enLigne()->count(),
            'boutique' => (clone $base)->boutique()->count(),
        ];

        $source = $request->query('source', 'toutes');
        if (! in_array($source, ['en_ligne', 'boutique'], true)) {
            $source = 'toutes';
        }

        // Double tri (date puis id) pour un ordre stable d'une page à l'autre.
        $requete = (clone $base)->with('lignes')->latest()->latest('id');

        if ($source === 'en_ligne') {
            $requete->enLigne();
        } elseif ($source === 'boutique') {
            $requete->boutique();
        }

        $commandes = $requete->paginate(self::PAR_PAGE)->withQueryString();

        return view('commandes', compact('commandes', 'source', 'compteurs'));
    }

    /**
     * Facture imprimable d'une commande (côté marchand).
     */
    public function facture(Commande $commande): View
    {
        abort_if($commande->user_id !== Auth::id(), 403);

        $commande->load('lignes');

        return view('facture', ['commande' => $commande, 'marchand' => Auth::user()]);
    }

    /**
     * Reçu PDF (A5) téléchargé directement par le client après sa commande.
     * Accessible sans connexion, identifié par le numéro unique (pas l'id)
     * pour ne pas exposer/deviner les commandes des autres clients.
     */
    public function recu(string $numero)
    {
        $commande = Commande::where('numero', $numero)->with(['lignes', 'user'])->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('recu', [
            'commande' => $commande,
            'marchand' => $commande->user,
        ])->setPaper('a5');

        return $pdf->download('recu-'.$commande->numero.'.pdf');
    }

    /**
     * Changement de statut par le marchand. Décrémente le stock des
     * produits une seule fois, au moment où la commande passe à "livrée".
     * Les ventes en boutique sont déjà livrées et payées : leur statut
     * n'est pas modifiable.
     */
    public function updateStatut(Request $request, Commande $commande): RedirectResponse
    {
        abort_if($commande->user_id !== Auth::id(), 403);
        abort_if($commande->estVenteBoutique(), 403, 'Le statut d\'une vente en boutique ne peut pas être modifié.');

        $validated = $request->validate([
            'statut' => ['required', 'in:a_prendre_en_compte,en_cours_de_livraison,livree'],
        ]);

        $commande->statut = $validated['statut'];

        if ($validated['statut'] === 'livree' && ! $commande->stock_decremente) {
            foreach ($commande->lignes as $ligne) {
                $produit = $ligne->produit;
                if ($produit && ! is_null($produit->stock)) {
                    $produit->decrement('stock', min($ligne->quantite, $produit->stock));
                }
            }
            $commande->stock_decremente = true;
        }

        $commande->save();

        return back()->with('status', 'Statut de la commande mis à jour.');
    }

    private function notifierMarchand(Commande $commande): void
    {
        /** @var User $marchand */
        $marchand = $commande->user;

        $destinataires = array_values(array_filter([
            $marchand->email_notification,
            $marchand->email_notification_secondaire,
        ]));

        if (empty($destinataires)) {
            $destinataires = [$marchand->email];
        }

        (new AnonymousNotifiable)
            ->route('mail', $destinataires)
            ->notify(new NouvelleCommandeNotification($commande));
    }
}