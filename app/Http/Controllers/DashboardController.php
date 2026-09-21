<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filtre = $request->query('filtre', 'toutes'); // 'toutes' | 'livrees'

        $produits = $user->produits();
        $commandes = $user->commandes();

        $stats = [
            'produits' => (clone $produits)->count(),
            'en_stock' => (clone $produits)->where(function ($query) {
                $query->whereNull('stock')->orWhere('stock', '>', 0);
            })->count(),
            'commandes' => (clone $commandes)->count(),
        ];

        $statutCounts = [
            'a_prendre_en_compte' => (clone $commandes)->where('statut', 'a_prendre_en_compte')->count(),
            'en_cours_de_livraison' => (clone $commandes)->where('statut', 'en_cours_de_livraison')->count(),
            'livree' => (clone $commandes)->where('statut', 'livree')->count(),
        ];

        $recentOrders = (clone $commandes)
            ->with('lignes')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($commande) => [
                'id' => $commande->numero,
                'date' => $commande->created_at->diffForHumans(),
                'items' => $commande->nombreArticles(),
                'total' => $commande->totalFormate(),
                'status' => $commande->statutLabel(),
            ])
            ->all();

        // ---- Graphique du nombre de commandes (14 derniers jours / 12 derniers mois) ----
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

        // ---- Chiffre d'affaires (respecte le filtre : toutes les commandes ou livrées uniquement) ----
        $commandesRevenu = clone $commandes;
        if ($filtre === 'livrees') {
            $commandesRevenu->where('statut', 'livree');
        }

        $revenus = [
            'jour' => (clone $commandesRevenu)->whereDate('created_at', now()->toDateString())->sum('total'),
            'mois' => (clone $commandesRevenu)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total'),
            'total' => (clone $commandesRevenu)->sum('total'),
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
            'user', 'stats', 'statutCounts', 'recentOrders',
            'graphJours', 'graphMois',
            'revenus', 'graphRevenuJours', 'graphRevenuMois', 'filtre'
        ));
    }
}