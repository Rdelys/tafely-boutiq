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
            'boutique_couleur' => ['required', 'in:'.implode(',', array_merge(array_keys(self::COULEURS), ['perso']))],
            'boutique_couleur_perso' => ['nullable', 'required_if:boutique_couleur,perso', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'boutique_description' => ['nullable', 'string', 'max:300'],
        ], [
            'boutique_theme.in' => 'Thème invalide.',
            'boutique_couleur.in' => 'Couleur invalide.',
            'boutique_couleur_perso.required_if' => 'Choisissez un code couleur, ou revenez à une couleur prédéfinie.',
            'boutique_couleur_perso.regex' => 'Le code couleur doit être au format #RRGGBB.',
            'boutique_description.max' => 'Le texte de présentation ne doit pas dépasser 300 caractères.',
        ]);

        // Si une couleur prédéfinie est choisie, on ne garde pas un ancien code perso.
        if ($validated['boutique_couleur'] !== 'perso') {
            $validated['boutique_couleur_perso'] = null;
        }

        Auth::user()->update($validated);

        return back()->with('status', 'Apparence de la boutique mise à jour.');
    }
}