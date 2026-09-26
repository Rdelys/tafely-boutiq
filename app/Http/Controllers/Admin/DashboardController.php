<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $utilisateurs = User::all(['id', 'status', 'abonnement_expire_le', 'created_at']);

        $actives = 0;
        $enEssai = 0;
        $essaiExpire = 0;

        foreach ($utilisateurs as $u) {
            if ($u->abonnementActif()) {
                $actives++;
            } elseif ($u->essaiExpire()) {
                $essaiExpire++;
            } else {
                $enEssai++;
            }
        }

        $stats = [
            'boutiques_total' => $utilisateurs->count(),
            'boutiques_actives' => $actives,
            'boutiques_essai' => $enEssai,
            'boutiques_essai_expire' => $essaiExpire,

            'revenu_total' => Paiement::where('statut', 'paye')->sum('montant'),

            'commandes_jour' => Commande::whereDate('created_at', now()->toDateString())->count(),
            'commandes_mois' => Commande::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // ---- MRR : revenus d'abonnements encaissés, par mois (12 derniers mois) ----
        $graphMrr = collect(range(11, 0))->map(function ($i) {
            $mois = now()->subMonths($i);

            $montant = Paiement::where('statut', 'paye')
                ->where('type', 'abonnement')
                ->whereYear('paye_le', $mois->year)
                ->whereMonth('paye_le', $mois->month)
                ->sum('montant');

            return ['label' => ucfirst($mois->translatedFormat('M Y')), 'total' => (int) $montant];
        });

        // ---- Conversion essai -> payant (cohorte des comptes créés ce mois-ci) ----
        $creesCeMois = $utilisateurs->filter(function ($u) {
            return $u->created_at->isSameMonth(now()) && $u->created_at->isSameYear(now());
        });
        $convertisCeMois = $creesCeMois->filter(fn ($u) => $u->abonnementActif())->count();
        $tauxConversion = $creesCeMois->count() > 0
            ? (int) round($convertisCeMois / $creesCeMois->count() * 100)
            : 0;

        // ---- Churn mensuel : parmi les abonnements arrivés à expiration ce mois-là,
        // combien n'ont PAS été renouvelés (12 derniers mois) ----
        $graphChurn = collect(range(11, 0))->map(function ($i) use ($utilisateurs) {
            $mois = now()->subMonths($i);

            $expirantCeMois = $utilisateurs->filter(function ($u) use ($mois) {
                return $u->abonnement_expire_le
                    && $u->abonnement_expire_le->isSameMonth($mois)
                    && $u->abonnement_expire_le->isSameYear($mois);
            });

            $total = $expirantCeMois->count();
            $churnes = $expirantCeMois->filter(fn ($u) => ! $u->abonnementActif())->count();

            return [
                'label' => ucfirst($mois->translatedFormat('M Y')),
                'taux' => $total > 0 ? (int) round($churnes / $total * 100) : 0,
            ];
        });

        // ---- Répartition des revenus : abonnements vs packs produits ----
        $repartitionRevenus = [
            'abonnements' => (int) Paiement::where('statut', 'paye')->where('type', 'abonnement')->sum('montant'),
            'packs' => (int) Paiement::where('statut', 'paye')->where('type', 'pack_produits')->sum('montant'),
        ];

        // ---- Nouvelles boutiques : par jour (14 derniers jours) et par semaine (12 dernières semaines) ----
        $nouvellesJour = collect(range(13, 0))->map(function ($i) use ($utilisateurs) {
            $jour = now()->subDays($i);

            return [
                'label' => $jour->format('d/m'),
                'total' => $utilisateurs->filter(fn ($u) => $u->created_at->isSameDay($jour))->count(),
            ];
        });

        $nouvellesSemaine = collect(range(11, 0))->map(function ($i) use ($utilisateurs) {
            $debut = now()->subWeeks($i)->startOfWeek();
            $fin = now()->subWeeks($i)->endOfWeek();

            return [
                'label' => $debut->format('d/m'),
                'total' => $utilisateurs->filter(fn ($u) => $u->created_at->between($debut, $fin))->count(),
            ];
        });

        return view('admin.dashboard', compact(
            'stats', 'graphMrr', 'tauxConversion', 'graphChurn',
            'repartitionRevenus', 'nouvellesJour', 'nouvellesSemaine'
        ));
    }
}