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
            'maintenance_inscriptions' => Settings::maintenanceInscriptions(),
            'maintenance_paiements' => Settings::maintenancePaiements(),
        ];

        return view('admin.parametres.index', compact('parametres'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prix_abonnement' => ['required', 'integer', 'min:0'],
            'prix_pack_produits' => ['required', 'integer', 'min:0'],
        ], [
            'prix_abonnement.required' => 'Merci d\'indiquer le prix de l\'abonnement.',
            'prix_pack_produits.required' => 'Merci d\'indiquer le prix du pack.',
        ]);

        Settings::set('prix_abonnement', (int) $validated['prix_abonnement']);
        Settings::set('prix_pack_produits', (int) $validated['prix_pack_produits']);
        Settings::set('maintenance_inscriptions', $request->boolean('maintenance_inscriptions'));
        Settings::set('maintenance_paiements', $request->boolean('maintenance_paiements'));

        return back()->with('status', 'Paramètres enregistrés.');
    }
}