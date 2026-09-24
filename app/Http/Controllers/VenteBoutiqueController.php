<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VenteBoutiqueController extends Controller
{
    /**
     * Écran "Nouvelle vente" (caisse) du marchand connecté.
     */
    public function create(): View
    {
        $produits = Auth::user()->produits()->orderBy('nom')->get();

        return view('ventes.creer', compact('produits'));
    }

    /**
     * Enregistre une vente faite en boutique physique.
     * Le nom et le téléphone du client sont facultatifs (client de passage).
     * La vente est enregistrée directement comme "livrée" et le stock est
     * décrémenté tout de suite. Aucun email n'est envoyé au vendeur.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $donnees = $request->validate([
            'nom_client' => ['nullable', 'string', 'max:255'],
            'telephone_client' => ['nullable', 'string', 'max:30'],
            'mode_paiement' => ['required', 'in:especes,mvola,orange_money,autre'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produit_id' => ['required', 'integer'],
            'items.*.quantite' => ['required', 'integer', 'min:1', 'max:999'],
        ], [
            'mode_paiement.required' => 'Merci de choisir un moyen de paiement.',
            'mode_paiement.in' => 'Moyen de paiement invalide.',
            'items.required' => 'Ajoutez au moins un produit à la vente.',
            'items.min' => 'Ajoutez au moins un produit à la vente.',
        ]);

        // Si le même produit apparaît deux fois, on additionne les quantités.
        $quantites = collect($donnees['items'])
            ->groupBy('produit_id')
            ->map(fn ($lignes) => (int) $lignes->sum('quantite'));

        $commande = DB::transaction(function () use ($donnees, $user, $quantites) {
            // Verrou sur les produits pour éviter de vendre deux fois le
            // dernier article (vente en boutique + commande en ligne simultanées).
            $produits = Produit::where('user_id', $user->id)
                ->whereIn('id', $quantites->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($produits->count() !== $quantites->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Certains produits sont introuvables.',
                ]);
            }

            $sousTotal = 0;
            $lignesAPreparer = [];

            foreach ($quantites as $produitId => $quantite) {
                $produit = $produits->get($produitId);

                if (! is_null($produit->stock) && $quantite > $produit->stock) {
                    throw ValidationException::withMessages([
                        'items' => $produit->stock <= 0
                            ? '« '.$produit->nom.' » est en rupture de stock.'
                            : 'Stock insuffisant pour « '.$produit->nom.' » (disponible : '.$produit->stock.').',
                    ]);
                }

                $sousTotalLigne = $produit->prix * $quantite;
                $sousTotal += $sousTotalLigne;

                $lignesAPreparer[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'donnees' => [
                        'produit_id' => $produit->id,
                        'nom_produit' => $produit->nom,
                        'prix_unitaire' => $produit->prix,
                        'quantite' => $quantite,
                        'sous_total' => $sousTotalLigne,
                    ],
                ];
            }

            $commande = Commande::create([
                'user_id' => $user->id,
                'source' => Commande::SOURCE_BOUTIQUE,
                'nom_client' => $donnees['nom_client'] ?? null,
                'telephone_client' => $donnees['telephone_client'] ?? null,
                'mode' => 'sur_place',
                'sous_total' => $sousTotal,
                'prix_livraison' => null,
                'total' => $sousTotal,
                'mode_paiement' => $donnees['mode_paiement'],
                'statut' => 'livree',
                'stock_decremente' => true,
            ]);

            foreach ($lignesAPreparer as $ligne) {
                $commande->lignes()->create($ligne['donnees']);

                if (! is_null($ligne['produit']->stock)) {
                    $ligne['produit']->decrement('stock', $ligne['quantite']);
                }
            }

            return $commande;
        });

        return response()->json([
            'message' => 'Vente enregistrée avec succès.',
            'numero' => $commande->numero,
            'total' => $commande->totalFormate(),
            'factureUrl' => route('commandes.facture', $commande),
        ]);
    }
}