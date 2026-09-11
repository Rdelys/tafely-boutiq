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
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CommandeController extends Controller
{
    /**
     * Réception d'une commande depuis la vitrine publique. Aucune connexion requise.
     */
    public function store(Request $request, Produit $produit): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nom_client' => ['required', 'string', 'max:255'],
            'telephone_client' => ['required', 'string', 'max:30'],
            'quantite' => ['required', 'integer', 'min:1', 'max:50'],
            'mode' => ['required', 'in:recuperer,livrer'],
            'date_recuperation' => ['required_if:mode,recuperer', 'nullable', 'date', 'after_or_equal:today'],
            'heure_recuperation' => ['required_if:mode,recuperer', 'nullable', 'string', 'max:10'],
            'adresse_livraison' => ['required_if:mode,livrer', 'nullable', 'string', 'max:500'],
        ], [
            'nom_client.required' => 'Merci d\'indiquer votre nom.',
            'telephone_client.required' => 'Merci d\'indiquer votre numéro de téléphone.',
            'date_recuperation.required_if' => 'Merci d\'indiquer une date de récupération.',
            'heure_recuperation.required_if' => 'Merci d\'indiquer une heure de récupération.',
            'adresse_livraison.required_if' => 'Merci d\'indiquer une adresse de livraison.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $donnees = $validator->validated();

        $prixLivraison = $donnees['mode'] === 'livrer' && $produit->aLivraison() ? $produit->prix_livraison : null;
        $total = ($produit->prix * $donnees['quantite']) + ($prixLivraison ?? 0);

        $commande = Commande::create([
            'user_id' => $produit->user_id,
            'produit_id' => $produit->id,
            'nom_client' => $donnees['nom_client'],
            'telephone_client' => $donnees['telephone_client'],
            'quantite' => $donnees['quantite'],
            'mode' => $donnees['mode'],
            'date_recuperation' => $donnees['date_recuperation'] ?? null,
            'heure_recuperation' => $donnees['heure_recuperation'] ?? null,
            'adresse_livraison' => $donnees['adresse_livraison'] ?? null,
            'prix_unitaire' => $produit->prix,
            'prix_livraison' => $prixLivraison,
            'total' => $total,
        ]);

        $this->notifierMarchand($commande);

        return response()->json([
            'message' => 'Commande envoyée avec succès.',
        ]);
    }

    /**
     * Liste des commandes du marchand connecté.
     */
    public function index(): View
    {
        $commandes = Auth::user()->commandes()->with('produit')->latest()->get();

        return view('commandes', compact('commandes'));
    }

    /**
     * Changement de statut par le marchand. Décrémente le stock une seule
     * fois, au moment où la commande passe au statut "livrée".
     */
    public function updateStatut(Request $request, Commande $commande): RedirectResponse
    {
        abort_if($commande->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'statut' => ['required', 'in:a_prendre_en_compte,en_cours_de_livraison,livree'],
        ]);

        $commande->statut = $validated['statut'];

        if ($validated['statut'] === 'livree' && ! $commande->stock_decremente) {
            $produit = $commande->produit;

            if ($produit && ! is_null($produit->stock)) {
                $produit->decrement('stock', min($commande->quantite, $produit->stock));
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