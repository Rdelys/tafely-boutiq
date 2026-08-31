<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // TODO: brancher ces chiffres sur les vrais modèles Produit / Commande
        // dès qu'ils existeront. Pour l'instant on affiche un état "vide/gratuit".
        $stats = [
            'produits' => 0,
            'en_stock' => 0,
            'commandes' => 0,
        ];

        $recentOrders = [];

        return view('dashboard', compact('user', 'stats', 'recentOrders'));
    }
}