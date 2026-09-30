<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\MarchandNotification;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'offre_lancement_active' => Settings::offreLancementActive(),
            'offre_lancement_places' => Settings::offreLancementPlaces(),
            'offre_lancement_mois' => Settings::offreLancementMois(),
        ];

        $apercu = Settings::detailDurees();

        // Les N premières boutiques inscrites, candidates à l'offre de lancement.
        $candidats = User::query()
            ->orderBy('id')
            ->limit(Settings::offreLancementPlaces())
            ->get(['id', 'nom_boutique', 'email', 'created_at', 'boutique_validee_le']);

        $nbValidees = $candidats->whereNotNull('boutique_validee_le')->count();

        return view('admin.parametres.index', compact('parametres', 'apercu', 'candidats', 'nbValidees'));
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
            'offre_lancement_places' => ['required', 'integer', 'min:0', 'max:1000'],
            'offre_lancement_mois' => ['required', 'integer', 'min:1', 'max:12'],
        ], [
            'prix_abonnement.required' => 'Merci d\'indiquer le prix de l\'abonnement.',
            'duree_max_mois.required' => 'Merci d\'indiquer la durée maximale.',
            'prix_par_produit.required' => 'Merci d\'indiquer le prix par produit supplémentaire.',
            'pas_produits.required' => 'Merci d\'indiquer le pas d\'achat des produits.',
            'quantite_max_produits.required' => 'Merci d\'indiquer la quantité maximale par achat.',
            'offre_lancement_places.required' => 'Merci d\'indiquer le nombre de places de l\'offre de lancement.',
            'offre_lancement_mois.required' => 'Merci d\'indiquer le nombre de mois offerts.',
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
        Settings::set('offre_lancement_active', $request->boolean('offre_lancement_active'));
        Settings::set('offre_lancement_places', (int) $validated['offre_lancement_places']);
        Settings::set('offre_lancement_mois', (int) $validated['offre_lancement_mois']);

        return back()->with('status', 'Paramètres enregistrés.');
    }

    /**
     * Valide une boutique éligible à l'offre de lancement : elle passe
     * tout de suite en plan payant, valable jusqu'à la fin de l'essai
     * gratuit + le nombre de mois offerts (donc 2 mois au total pour
     * 1 mois offert, si la validation a lieu pendant l'essai).
     */
    public function validerBoutique(User $utilisateur): RedirectResponse
    {
        if (! Settings::offreLancementActive()) {
            return back()->with('erreur', 'L\'offre de lancement est désactivée.')->with('onglet', 'validation');
        }

        if ($utilisateur->boutiqueValidee()) {
            return back()->with('erreur', 'Cette boutique est déjà validée.')->with('onglet', 'validation');
        }

        if (! $utilisateur->estEligibleOffreLancement()) {
            return back()->with('erreur', 'Cette boutique ne fait pas partie des premières inscrites.')->with('onglet', 'validation');
        }

        $mois = Settings::offreLancementMois();

        // Le mois offert commence à la fin de l'essai gratuit (ou à la fin
        // de l'abonnement en cours s'il est plus tard).
        $depart = now();

        if ($utilisateur->finEssaiLe()->greaterThan($depart)) {
            $depart = $utilisateur->finEssaiLe();
        }

        if ($utilisateur->abonnementActif() && $utilisateur->abonnement_expire_le && $utilisateur->abonnement_expire_le->greaterThan($depart)) {
            $depart = $utilisateur->abonnement_expire_le->copy();
        }

        $fin = $depart->copy()->addMonths($mois);

        $utilisateur->update([
            'status' => 'active',
            'abonnement_expire_le' => $fin,
            'boutique_validee_le' => now(),
        ]);

        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'user_id' => $utilisateur->id,
            'action' => 'validation_boutique',
            'details' => ['mois_offerts' => $mois, 'fin_abonnement' => $fin->format('d/m/Y')],
        ]);

        MarchandNotification::create([
            'user_id' => $utilisateur->id,
            'type' => 'boutique_validee',
            'titre' => 'Boutique validée — '.$mois.' mois offert'.($mois > 1 ? 's' : ''),
            'message' => 'Votre boutique a été validée par l\'équipe Tafely. Vous bénéficiez du plan payant gratuitement, '
                .'en plus de votre essai gratuit, jusqu\'au '.$fin->format('d/m/Y').'. '
                .'Vous avez accès à toutes les fonctions du plan payant.',
        ]);

        return back()
            ->with('status', 'Boutique validée : '.$mois.' mois offert'.($mois > 1 ? 's' : '').', valable jusqu\'au '.$fin->format('d/m/Y').'.')
            ->with('onglet', 'validation');
    }
}