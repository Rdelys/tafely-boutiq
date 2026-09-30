<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Nombre d'éléments par page dans "Activité récente".
     */
    private const ACTIVITE_PAR_PAGE = 5;

    public function index(Request $request)
    {
        $user = Auth::user();

        $filtre = $request->query('filtre', 'toutes'); // 'toutes' | 'livrees'
        if ($filtre !== 'livrees') {
            $filtre = 'toutes';
        }

        $source = $request->query('source', 'toutes'); // 'toutes' | 'en_ligne' | 'boutique'
        if (! in_array($source, ['en_ligne', 'boutique'], true)) {
            $source = 'toutes';
        }

        $produits = $user->produits();
        $commandes = $user->commandes();

        $nbEnLigne = (clone $commandes)->enLigne()->count();
        $nbBoutique = (clone $commandes)->boutique()->count();

        $stats = [
            'produits' => (clone $produits)->count(),
            'en_stock' => (clone $produits)->where(function ($query) {
                $query->whereNull('stock')->orWhere('stock', '>', 0);
            })->count(),
            'commandes' => $nbEnLigne + $nbBoutique,
            'commandes_en_ligne' => $nbEnLigne,
            'ventes_boutique' => $nbBoutique,
        ];

        // Le suivi par statut ne concerne que les commandes en ligne :
        // les ventes en boutique sont directement enregistrées comme livrées.
        $statutCounts = [
            'a_prendre_en_compte' => (clone $commandes)->enLigne()->where('statut', 'a_prendre_en_compte')->count(),
            'en_cours_de_livraison' => (clone $commandes)->enLigne()->where('statut', 'en_cours_de_livraison')->count(),
            'livree' => (clone $commandes)->enLigne()->where('statut', 'livree')->count(),
        ];

        // ---- Activité récente (paginée) ----
        $activite = (clone $commandes)
            ->with('lignes')
            ->latest()
            ->latest('id')
            ->paginate(self::ACTIVITE_PAR_PAGE, ['*'], 'activite')
            ->through(fn ($commande) => [
                'id' => $commande->numero,
                'client' => $commande->nomClientAffiche(),
                'source' => $commande->source,
                'date' => $commande->created_at->diffForHumans(),
                'items' => $commande->nombreArticles(),
                'total' => $commande->totalFormate(),
                'status' => $commande->statutLabel(),
            ])
            ->withQueryString()
            ->fragment('activite');

        // ---- Graphique du nombre de commandes et ventes (14 derniers jours / 12 derniers mois) ----
        $commandesJour = (clone $commandes)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at']);

        $graphJours = collect(range(13, 0))->map(function ($i) use ($commandesJour) {
            $jour = now()->subDays($i);

            return [
                'label' => $jour->format('d/m'),
                'total' => $commandesJour->filter(fn ($c) => $c->created_at->isSameDay($jour))->count(),
            ];
        });

        $commandesMois = (clone $commandes)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at']);

        $graphMois = collect(range(11, 0))->map(function ($i) use ($commandesMois) {
            $mois = now()->subMonths($i);

            return [
                'label' => ucfirst($mois->translatedFormat('M Y')),
                'total' => $commandesMois->filter(fn ($c) => $c->created_at->isSameMonth($mois))->count(),
            ];
        });

        // ---- Chiffre d'affaires ----
        // Base : toutes les commandes ou livrées uniquement (filtre "statut").
        $commandesRevenuBase = clone $commandes;
        if ($filtre === 'livrees') {
            $commandesRevenuBase->where('statut', 'livree');
        }

        // Puis, filtre "origine" : toutes, en ligne ou boutique.
        $commandesRevenu = clone $commandesRevenuBase;
        if ($source === 'en_ligne') {
            $commandesRevenu->enLigne();
        } elseif ($source === 'boutique') {
            $commandesRevenu->boutique();
        }

        $revenus = [
            'jour' => (clone $commandesRevenu)->whereDate('created_at', now()->toDateString())->sum('total'),
            'mois' => (clone $commandesRevenu)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total'),
            'total' => (clone $commandesRevenu)->sum('total'),
        ];

        // ---- Répartition du mois en cours : en ligne vs boutique ----
        // (respecte le filtre "statut", pas le filtre "origine")
        $repartitionBrute = (clone $commandesRevenuBase)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('source, COUNT(*) as nombre, SUM(total) as montant')
            ->groupBy('source')
            ->get()
            ->keyBy('source');

        $montantEnLigne = (int) ($repartitionBrute->get('en_ligne')?->montant ?? 0);
        $montantBoutique = (int) ($repartitionBrute->get('boutique')?->montant ?? 0);
        $montantRepartition = $montantEnLigne + $montantBoutique;
        $pourcentEnLigne = $montantRepartition > 0 ? (int) round($montantEnLigne / $montantRepartition * 100) : 0;

        $repartition = [
            'en_ligne' => [
                'montant' => $montantEnLigne,
                'nombre' => (int) ($repartitionBrute->get('en_ligne')?->nombre ?? 0),
                'pourcent' => $pourcentEnLigne,
            ],
            'boutique' => [
                'montant' => $montantBoutique,
                'nombre' => (int) ($repartitionBrute->get('boutique')?->nombre ?? 0),
                'pourcent' => $montantRepartition > 0 ? 100 - $pourcentEnLigne : 0,
            ],
            'total' => $montantRepartition,
        ];

        $revenuJourBrut = (clone $commandesRevenu)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at', 'total']);

        $graphRevenuJours = collect(range(13, 0))->map(function ($i) use ($revenuJourBrut) {
            $jour = now()->subDays($i);

            return [
                'label' => $jour->format('d/m'),
                'total' => $revenuJourBrut->filter(fn ($c) => $c->created_at->isSameDay($jour))->sum('total'),
            ];
        });

        $revenuMoisBrut = (clone $commandesRevenu)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at', 'total']);

        $graphRevenuMois = collect(range(11, 0))->map(function ($i) use ($revenuMoisBrut) {
            $mois = now()->subMonths($i);

            return [
                'label' => ucfirst($mois->translatedFormat('M Y')),
                'total' => $revenuMoisBrut->filter(fn ($c) => $c->created_at->isSameMonth($mois))->sum('total'),
            ];
        });

        return view('dashboard', compact(
            'user', 'stats', 'statutCounts', 'activite',
            'graphJours', 'graphMois',
            'revenus', 'repartition', 'graphRevenuJours', 'graphRevenuMois',
            'filtre', 'source'
        ));
    }
}