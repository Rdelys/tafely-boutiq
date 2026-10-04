<?php

namespace App\Http\Middleware;

use App\Models\Visite;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compte les visites des pages publiques (accueil, vitrines, aide, contact,
 * confidentialité) APRÈS l'ouverture du site. Ignore les robots, les marchands
 * et admins connectés, et une même page rechargée dans les 30 minutes.
 */
class EnregistrerVisite
{
    public function handle(Request $request, Closure $next): Response
    {
        $reponse = $next($request);

        try {
            if ($this->doitEnregistrer($request, $reponse)) {
                $this->enregistrer($request);
            }
        } catch (\Throwable $e) {
            // Le suivi ne doit jamais casser le site.
            report($e);
        }

        return $reponse;
    }

    private function doitEnregistrer(Request $request, Response $reponse): bool
    {
        if (! $request->isMethod('GET') || $reponse->getStatusCode() !== 200) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        // Pas de comptage avant l'ouverture officielle.
        $ouverture = Carbon::parse(config('lancement.date'), config('lancement.fuseau'));
        if (now()->lessThan($ouverture)) {
            return false;
        }

        // Pages publiques seulement.
        if (! $request->is('/', 'b/*', 'aide', 'contact', 'confidentialite')) {
            return false;
        }

        // Marchands / admins connectés : ce ne sont pas des visiteurs.
        if (Auth::check() || Auth::guard('admin')->check()) {
            return false;
        }

        return ! $this->estUnRobot((string) $request->userAgent());
    }

    private function estUnRobot(string $ua): bool
    {
        if ($ua === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|headless|monitor|uptime|lighthouse/i',
            $ua
        );
    }

    private function enregistrer(Request $request): void
    {
        $chemin = '/'.ltrim($request->path(), '/');
        $visiteur = hash('sha256', $request->ip().'|'.$request->userAgent().'|'.config('app.key'));

        // Même visiteur + même page : une seule visite toutes les 30 minutes.
        if (! Cache::add('visite:'.$visiteur.':'.$chemin, 1, now()->addMinutes(30))) {
            return;
        }

        Visite::create([
            'visiteur' => $visiteur,
            'chemin' => mb_substr($chemin, 0, 255),
            'type' => $request->is('/') ? 'accueil' : ($request->is('b/*') ? 'vitrine' : 'page'),
            'source' => $this->source($request),
        ]);
    }

    private function source(Request $request): string
    {
        $hote = strtolower((string) parse_url((string) $request->headers->get('referer'), PHP_URL_HOST));

        return match (true) {
            $hote === '' => 'Direct',
            str_contains($hote, 'facebook') || str_contains($hote, 'fb.') || str_contains($hote, 'messenger') => 'Facebook',
            str_contains($hote, 'whatsapp') || str_contains($hote, 'wa.me') => 'WhatsApp',
            str_contains($hote, 'instagram') => 'Instagram',
            str_contains($hote, 'google') => 'Google',
            $hote === strtolower($request->getHost()) => 'Direct',
            default => 'Autre',
        };
    }
}