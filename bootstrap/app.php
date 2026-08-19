<?php
// Fichier bootstrap/app.php de Laravel 13 — copiez seulement les parties
// signalées ci-dessous dans votre bootstrap/app.php existant.

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Redirige les visiteurs non connectés vers la page d'accueil
        // (et non vers une route "login" qui n'existe pas ici, puisque
        // la connexion se fait uniquement via la modal de la homepage).
        $middleware->redirectGuestsTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
