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
            'prix_pack_produits' => Settings::prixPackProduits(),
            'duree_max_mois' => Settings::dureeMaxMois(),
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
            'prix_pack_produits' => ['required', 'integer', 'min:0'],
            'duree_max_mois' => ['required', 'integer', 'min:1', 'max:120'],
            'reduction_trimestre' => ['required', 'integer', 'min:0', 'max:90'],
            'reduction_semestre' => ['required', 'integer', 'min:0', 'max:90'],
            'reduction_9_mois' => ['required', 'integer', 'min:0', 'max:90'],
            'reduction_annuel' => ['required', 'integer', 'min:0', 'max:90'],
        ], [
            'prix_abonnement.required' => 'Merci d\'indiquer le prix de l\'abonnement.',
            'prix_pack_produits.required' => 'Merci d\'indiquer le prix du pack.',
            'duree_max_mois.required' => 'Merci d\'indiquer la durée maximale.',
        ]);

        // Cohérence : chaque palier doit être au moins aussi avantageux que
        // le précédent (le trimestre ne peut pas coûter plus cher que le mensuel, etc.).
        if (
            $validated['reduction_trimestre'] > $validated['reduction_semestre']
            || $validated['reduction_semestre'] > $validated['reduction_9_mois']
            || $validated['reduction_9_mois'] > $validated['reduction_annuel']
        ) {
            return back()->withInput()->with('erreur', 'Chaque palier doit offrir une réduction égale ou supérieure au précédent (trimestre ≤ semestre ≤ 9 mois ≤ annuel).');
        }

        Settings::set('prix_abonnement', (int) $validated['prix_abonnement']);
        Settings::set('prix_pack_produits', (int) $validated['prix_pack_produits']);
        Settings::set('duree_max_mois', (int) $validated['duree_max_mois']);
        Settings::set('reduction_trimestre', (int) $validated['reduction_trimestre']);
        Settings::set('reduction_semestre', (int) $validated['reduction_semestre']);
        Settings::set('reduction_9_mois', (int) $validated['reduction_9_mois']);
        Settings::set('reduction_annuel', (int) $validated['reduction_annuel']);
        Settings::set('maintenance_inscriptions', $request->boolean('maintenance_inscriptions'));
        Settings::set('maintenance_paiements', $request->boolean('maintenance_paiements'));

        return back()->with('status', 'Paramètres enregistrés.');
    }
}