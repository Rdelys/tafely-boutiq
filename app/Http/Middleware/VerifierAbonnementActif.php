<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifierAbonnementActif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->essaiExpire()) {
            return redirect()->route('abonnement')
                ->with('erreur', "Votre période d'essai gratuite est terminée. Souscrivez à l'abonnement pour continuer.");
        }

        return $next($request);
    }
}