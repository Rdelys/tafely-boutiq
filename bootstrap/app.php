<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // Page de compte à rebours avant le lancement (production uniquement).
        // Affichée à tout le monde, même avec une session ouverte.
        $middleware->web(append: [
            \App\Http\Middleware\PageLancement::class,
        ]);

        // Exceptions CSRF
        // Le callback de paiement n'est pas protégé par le CSRF
        $middleware->validateCsrfTokens(except: [
            'abonnement/paiement/*/callback',
        ]);

        // Alias des middlewares personnalisés
        $middleware->alias([
            'abonnement.actif' => \App\Http\Middleware\VerifierAbonnementActif::class,
            //'compte.actif' => \App\Http\Middleware\VerifierCompteActif::class,
        ]);

        // Redirection des visiteurs non connectés
        //
        // Administration :
        // /admin/* -> page de connexion admin
        //
        // Autres pages :
        // -> accueil
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin*')
                ? route('admin.login')
                : route('home')
        );
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->create();