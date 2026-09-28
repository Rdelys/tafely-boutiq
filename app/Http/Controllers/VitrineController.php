<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class VitrineController extends Controller
{
    public function show(string $identifiant): View
    {
        $user = User::where('pseudo', $identifiant)->first()
            ?? User::where('slug', $identifiant)->first();

        if (! $user && ctype_digit($identifiant)) {
            $user = User::find((int) $identifiant);
        }

        abort_if(! $user, 404);

        $produits = $user->produits()->visibles()->latest()->get();
        
        return view('vitrine', [
            'marchand' => $user,
            'produits' => $produits,
            'couleurAccent' => $user->couleurBoutique(),
            'commanderUrl' => route('commandes.store', $identifiant),
        ]);
    }
}