<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $produits = $user->produits();

        $stats = [
            'produits' => (clone $produits)->count(),
            'en_stock' => (clone $produits)->where(function ($query) {
                $query->whereNull('stock')->orWhere('stock', '>', 0);
            })->count(),
            'commandes' => $user->commandes()->count(),
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

        return view('dashboard', compact('user', 'stats', 'recentOrders'));
    }
}