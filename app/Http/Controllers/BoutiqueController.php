<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BoutiqueController extends Controller
{
    public const THEMES = [
        'classique' => ['nom' => 'Classique', 'description' => 'Cartes nettes, coins arrondis discrets.'],
        'moderne' => ['nom' => 'Moderne', 'description' => 'Ombres marquées, coins bien arrondis.'],
        'minimal' => ['nom' => 'Minimal', 'description' => 'Épuré, sans bordures ni ombres.'],
    ];

    public const COULEURS = [
        'bleu' => '#2563eb',
        'rouge' => '#dc2626',
        'vert' => '#16a34a',
        'violet' => '#7c3aed',
        'orange' => '#ea580c',
        'noir' => '#111827',
    ];

    public function edit(): View
    {
        $user = Auth::user();
        $produits = $user->produits()->latest()->get();

        return view('boutique', [
            'user' => $user,
            'produits' => $produits,
            'themes' => self::THEMES,
            'couleurs' => self::COULEURS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'boutique_theme' => ['required', 'in:'.implode(',', array_keys(self::THEMES))],
            'boutique_couleur' => ['required', 'in:'.implode(',', array_keys(self::COULEURS))],
        ], [
            'boutique_theme.in' => 'Thème invalide.',
            'boutique_couleur.in' => 'Couleur invalide.',
        ]);

        Auth::user()->update($validated);

        return back()->with('status', 'Apparence de la boutique mise à jour.');
    }
}