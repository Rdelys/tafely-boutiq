<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageLancement
{
    private const COOKIE = 'lancement_acces';

    public function handle(Request $request, Closure $next): Response
    {
        $ouverture = Carbon::parse(config('lancement.date'), config('lancement.fuseau'));

        // Hors production, ou lancement passé : le site fonctionne normalement.
        if (! app()->environment('production') || now()->greaterThanOrEqualTo($ouverture)) {
            return $next($request);
        }

        // Toujours accessibles : l'admin, le callback de paiement, la santé du serveur, le SEO.
        if ($request->is('admin', 'admin/*', 'abonnement/paiement/*', 'up', 'sitemap.xml', 'robots.txt')) {
            return $next($request);
        }

        // Accès équipe par clé secrète (facultatif).
        $cle = (string) config('lancement.cle_acces');

        if ($cle !== '') {
            if (hash_equals($cle, (string) $request->query('acces'))) {
                return redirect($request->url())
                    ->withCookie(cookie(self::COOKIE, $this->jeton($cle), 60 * 24 * 7));
            }

            if (hash_equals($this->jeton($cle), (string) $request->cookie(self::COOKIE))) {
                return $next($request);
            }
        }

        $secondes = max(0, $ouverture->getTimestamp() - time());

        $entetes = [
            'Retry-After' => $secondes,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ];

        // Appels JavaScript (connexion, commande...) : réponse JSON.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tafely ouvre bientôt. Rendez-vous le '.$this->libelle($ouverture).'.',
            ], 503, $entetes);
        }

        return response()->view('lancement', [
            'secondes' => $secondes,
            'dateLibelle' => $this->libelle($ouverture),
        ], 503, $entetes);
    }

    private function jeton(string $cle): string
    {
        return hash_hmac('sha256', 'lancement-acces', $cle);
    }

    private function libelle(Carbon $date): string
    {
        // Ex : "lundi 5 octobre 2026 à 08h00"
        return $date->copy()->locale('fr')->translatedFormat('l j F Y à H\hi');
    }
}