<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Affichage des prix selon le pays du visiteur.
 *
 *  - Madagascar (et tous les autres pays pour le moment) : prix en Ariary.
 *  - France : prix en euros = prix en Ariary ÷ cours de change (Ar pour 1 €).
 *
 * Le montant réellement facturé reste TOUJOURS en Ariary (via Papi) : l'euro n'est
 * qu'un prix affiché à titre indicatif.
 */
class GeoPricingService
{
    private const SESSION_CLE = 'pays_prix';

    /** Pays pour lesquels un prix spécifique est affiché. Tout autre pays => Madagascar. */
    private const PAYS_EURO = ['FR'];

    /**
     * Contexte de prix du visiteur : pays, devise, taux (Ar pour 1 unité de la devise).
     *
     * @return array{pays: string, code: string, taux: float}
     */
    public function contexte(Request $request): array
    {
        if ($this->detecterPays($request) === 'FR') {
            return ['pays' => 'FR', 'code' => 'EUR', 'taux' => $this->tauxEurMga()];
        }

        return ['pays' => 'MG', 'code' => 'MGA', 'taux' => 1.0];
    }

    /**
     * Compatibilité avec l'ancien appel : $pricingService->getPrice($request->ip()).
     */
    public function getPrice(?string $ip = null): array
    {
        return $this->contexte(request());
    }

    /**
     * Formate un montant en Ariary dans la devise du contexte.
     * Ex : 20000 => "20 000 Ar" (MG) ou "4,00 €" (FR, si 1 € = 5 000 Ar).
     */
    public function formater(int $montantAr, array $contexte): string
    {
        if ($contexte['code'] === 'EUR') {
            return number_format($montantAr / $contexte['taux'], 2, ',', ' ').' €';
        }

        return number_format($montantAr, 0, ',', ' ').' Ar';
    }

    /**
     * Cours du jour : nombre d'Ariary pour 1 €. Mis en cache 6 h ;
     * en cas de panne de l'API, on utilise TAUX_EUR_MGA (.env).
     */
    public function tauxEurMga(): float
    {
        $enCache = Cache::get('taux_eur_mga');
        if ($enCache) {
            return (float) $enCache;
        }

        $secours = (float) config('services.geo.taux_eur_mga', 5000);

        // Après un échec, on ne réessaie pas avant 10 minutes.
        if (Cache::has('taux_eur_mga_echec')) {
            return $secours;
        }

        try {
            $taux = (float) Http::timeout(3)->get('https://open.er-api.com/v6/latest/EUR')->json('rates.MGA');

            if ($taux > 100) {
                $taux = round($taux, 2);
                Cache::put('taux_eur_mga', $taux, now()->addHours(6));

                return $taux;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        Cache::put('taux_eur_mga_echec', true, now()->addMinutes(10));

        return $secours;
    }

    // ------------------------------------------------------------------

    private function detecterPays(Request $request): string
    {
        // 1. Forçage manuel (test, ou visiteur mal localisé) : ?pays=FR ou ?pays=MG
        $force = strtoupper((string) $request->query('pays'));
        if (in_array($force, ['FR', 'MG'], true)) {
            $request->session()->put(self::SESSION_CLE, $force);

            return $force;
        }

        // 2. Pays déjà détecté pendant cette session : reste stable.
        $session = $request->session()->get(self::SESSION_CLE);
        if (in_array($session, ['FR', 'MG'], true)) {
            return $session;
        }

        $pays = $this->normaliser($this->paysDepuisEnTete($request) ?? $this->paysDepuisIp((string) $request->ip()));
        $request->session()->put(self::SESSION_CLE, $pays);

        return $pays;
    }

    private function normaliser(?string $codePays): string
    {
        return in_array(strtoupper((string) $codePays), self::PAYS_EURO, true) ? 'FR' : 'MG';
    }

    /** Si le site est derrière Cloudflare, le pays est déjà fourni dans l'en-tête. */
    private function paysDepuisEnTete(Request $request): ?string
    {
        $code = strtoupper((string) $request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $code) && ! in_array($code, ['XX', 'T1'], true) ? $code : null;
    }

    private function paysDepuisIp(string $ip): ?string
    {
        // IP locale / privée (développement) : Madagascar par défaut.
        if ($ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        return Cache::remember('geo_ip:'.$ip, now()->addDay(), function () use ($ip) {
            try {
                $code = Http::timeout(2)->get('https://ipwho.is/'.$ip)->json('country_code');

                return is_string($code) ? $code : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}