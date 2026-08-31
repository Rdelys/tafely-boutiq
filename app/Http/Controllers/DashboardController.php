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
            // TODO: brancher sur le vrai modèle Commande dès qu'il existera.
            'commandes' => 0,
        ];

        $recentOrders = [];

        return view('dashboard', compact('user', 'stats', 'recentOrders'));
    }
}