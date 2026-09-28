<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParametresController extends Controller
{
    public function index(): View
    {
        $parametres = [
            'prix_abonnement' => Settings::prixAbonnement(),
            'duree_max_mois' => Settings::dureeMaxMois(),
            'prix_par_produit' => Settings::prixParProduit(),
            'pas_produits' => Settings::pasProduits(),
            'quantite_max_produits' => Settings::quantiteMaxProduits(),
            'reduction_trimestre' => Settings::get('reduction_trimestre'),
            'reduction_semestre' => Settings::get('reduction_semestre'),
            'reduction_9_mois' => Settings::get('reduction_9_mois'),
            'reduction_annuel' => Settings::get('reduction_annuel'),
            'maintenance_inscriptions' => Settings::maintenanceInscriptions(),
            'maintenance_paiements' => Settings::maintenancePaiements(),
        ];

        $apercu = Settings::detailDurees();

        return view('admin.parametres.index', compact('parametres', 'apercu'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prix_abonnement' => ['required', 'integer', 'min:0'],
            'duree_max_mois' => ['required', 'integer', 'min:1', 'max:120'],
            'prix_par_produit' => ['required', 'integer', 'min:0'],
            'pas_produits' => ['required', 'integer', 'min:1', 'max:100'],
            'quantite_max_produits' => ['required', 'integer', 'min:1', 'max:1000'],
            'reduction_trimestre' => ['required', 'integer', 'min:0', 'max:90'],
            'reduction_semestre' => ['required', 'integer', 'min:0', 'max:90'],
            'reduction_9_mois' => ['required', 'integer', 'min:0', 'max:90'],
            'reduction_annuel' => ['required', 'integer', 'min:0', 'max:90'],
        ], [
            'prix_abonnement.required' => 'Merci d\'indiquer le prix de l\'abonnement.',
            'duree_max_mois.required' => 'Merci d\'indiquer la durée maximale.',
            'prix_par_produit.required' => 'Merci d\'indiquer le prix par produit supplémentaire.',
            'pas_produits.required' => 'Merci d\'indiquer le pas d\'achat des produits.',
            'quantite_max_produits.required' => 'Merci d\'indiquer la quantité maximale par achat.',
        ]);

        // Cohérence : chaque palier doit être au moins aussi avantageux que le précédent.
        if (
            $validated['reduction_trimestre'] > $validated['reduction_semestre']
            || $validated['reduction_semestre'] > $validated['reduction_9_mois']
            || $validated['reduction_9_mois'] > $validated['reduction_annuel']
        ) {
            return back()->withInput()->with('erreur', 'Chaque palier doit offrir une réduction égale ou supérieure au précédent (trimestre ≤ semestre ≤ 9 mois ≤ annuel).');
        }

        // La quantité maximale doit permettre d'acheter au moins un pas.
        if ($validated['quantite_max_produits'] < $validated['pas_produits']) {
            return back()->withInput()->with('erreur', 'La quantité maximale par achat doit être au moins égale au pas d\'achat.');
        }

        Settings::set('prix_abonnement', (int) $validated['prix_abonnement']);
        Settings::set('duree_max_mois', (int) $validated['duree_max_mois']);
        Settings::set('prix_par_produit', (int) $validated['prix_par_produit']);
        Settings::set('pas_produits', (int) $validated['pas_produits']);
        Settings::set('quantite_max_produits', (int) $validated['quantite_max_produits']);
        Settings::set('reduction_trimestre', (int) $validated['reduction_trimestre']);
        Settings::set('reduction_semestre', (int) $validated['reduction_semestre']);
        Settings::set('reduction_9_mois', (int) $validated['reduction_9_mois']);
        Settings::set('reduction_annuel', (int) $validated['reduction_annuel']);
        Settings::set('maintenance_inscriptions', $request->boolean('maintenance_inscriptions'));
        Settings::set('maintenance_paiements', $request->boolean('maintenance_paiements'));

        return back()->with('status', 'Paramètres enregistrés.');
    }
}