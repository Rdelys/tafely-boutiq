<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

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

        $recentOrders = $user->commandes()
            ->with('produit')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($commande) => [
                'id' => $commande->id,
                'date' => $commande->created_at->diffForHumans(),
                'items' => $commande->quantite,
                'total' => $commande->totalFormate(),
                'status' => $commande->statutLabel(),
            ])
            ->all();

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

        return view('dashboard', compact('user', 'stats', 'statutCounts', 'recentOrders', 'graphJours', 'graphMois'));
    }
}