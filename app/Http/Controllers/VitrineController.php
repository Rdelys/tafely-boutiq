<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\View\View;

class VitrineController extends Controller
{
    public function show(string $identifiant): View|Response
    {
        $user = User::where('pseudo', $identifiant)->first()
            ?? User::where('slug', $identifiant)->first();

        if (! $user && ctype_digit($identifiant)) {
            $user = User::find((int) $identifiant);
        }

        abort_if(! $user, 404);

        // Boutique suspendue : la vitrine n'est plus accessible au public.
        if ($user->estSuspendu()) {
            return response()->view('vitrine-indisponible', ['marchand' => $user], 403);
        }

        $produits = $user->produits()->visibles()->latest()->get();

        return view('vitrine', [
            'marchand' => $user,
            'produits' => $produits,
            'couleurAccent' => $user->couleurBoutique(),
            'commanderUrl' => route('commandes.store', $identifiant),
        ]);
    }
}