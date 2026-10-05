<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Affichage des prix selon le pays du visiteur :
 *
 *  - Madagascar  : Ariary (Ar)
 *  - France      : euros (€)  = prix en Ariary ÷ cours EUR/MGA
 *  - Autres pays : dollars ($) = prix en Ariary ÷ cours USD/MGA
 *
 * Le montant réellement facturé reste TOUJOURS en Ariary (via Papi) : l'euro et le
 * dollar ne sont que des prix affichés à titre indicatif.
 */
class GeoPricingService
{
    private const SESSION_CLE = 'pays_prix';

    /**
     * Contexte de prix du visiteur.
     *
     * @return array{pays: string, code: string, taux: float, symbole: string, nom: string}
     */
    public function contexte(Request $request): array
    {
        return match ($this->detecterPays($request)) {
            'FR' => ['pays' => 'FR', 'code' => 'EUR', 'taux' => $this->taux('EUR'), 'symbole' => '€', 'nom' => 'euros'],
            'INT' => ['pays' => 'INT', 'code' => 'USD', 'taux' => $this->taux('USD'), 'symbole' => '$', 'nom' => 'dollars'],
            default => ['pays' => 'MG', 'code' => 'MGA', 'taux' => 1.0, 'symbole' => 'Ar', 'nom' => 'Ariary'],
        };
    }

    /** Compatibilité avec l'ancien appel : $pricingService->getPrice($request->ip()). */
    public function getPrice(?string $ip = null): array
    {
        return $this->contexte(request());
    }

    /**
     * Formate un montant en Ariary dans la devise du contexte.
     * Ex : 20000 => "20 000 Ar" (MG), "4,00 €" (FR), "$4.44" (autres pays).
     */
    public function formater(int $montantAr, array $contexte): string
    {
        return match ($contexte['code']) {
            'EUR' => number_format($montantAr / $contexte['taux'], 2, ',', ' ').' €',
            'USD' => '$'.number_format($montantAr / $contexte['taux'], 2, '.', ','),
            default => number_format($montantAr, 0, ',', ' ').' Ar',
        };
    }

    /**
     * Cours du jour : nombre d'Ariary pour 1 EUR ou 1 USD. Mis en cache 6 h ;
     * si l'API de change est injoignable, on utilise le cours de secours du .env.
     */
    public function taux(string $devise): float
    {
        $cle = 'taux_'.strtolower($devise).'_mga';

        $enCache = Cache::get($cle);
        if ($enCache) {
            return (float) $enCache;
        }

        $secours = (float) config('services.geo.taux_'.strtolower($devise).'_mga', $devise === 'USD' ? 4500 : 5000);

        // Après un échec, on ne réessaie pas avant 10 minutes.
        if (Cache::has($cle.'_echec')) {
            return $secours;
        }

        try {
            $taux = (float) Http::timeout(3)->get('https://open.er-api.com/v6/latest/'.$devise)->json('rates.MGA');

            if ($taux > 100) {
                $taux = round($taux, 2);
                Cache::put($cle, $taux, now()->addHours(6));

                return $taux;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        Cache::put($cle.'_echec', true, now()->addMinutes(10));

        return $secours;
    }

    // ------------------------------------------------------------------

    /** @return string 'MG' | 'FR' | 'INT' (international : dollars) */
    private function detecterPays(Request $request): string
    {
        // 1. Forçage manuel (test, ou visiteur mal localisé) : ?pays=FR, ?pays=MG ou ?pays=US
        $force = strtoupper((string) $request->query('pays'));
        if (in_array($force, ['FR', 'MG', 'US'], true)) {
            $pays = $force === 'US' ? 'INT' : $force;
            $request->session()->put(self::SESSION_CLE, $pays);

            return $pays;
        }

        // 2. Pays déjà détecté pendant cette session : reste stable.
        $session = $request->session()->get(self::SESSION_CLE);
        if (in_array($session, ['FR', 'MG', 'INT'], true)) {
            return $session;
        }

        $pays = $this->normaliser($this->paysDepuisEnTete($request) ?? $this->paysDepuisIp((string) $request->ip()));
        $request->session()->put(self::SESSION_CLE, $pays);

        return $pays;
    }

    /** Pays inconnu (détection impossible, développement local) : Madagascar. */
    private function normaliser(?string $codePays): string
    {
        return match (strtoupper((string) $codePays)) {
            '' => 'MG',
            'MG' => 'MG',
            'FR' => 'FR',
            default => 'INT',
        };
    }

    /** Si le site est derrière Cloudflare, le pays est déjà fourni dans l'en-tête. */
    private function paysDepuisEnTete(Request $request): ?string
    {
        $code = strtoupper((string) $request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $code) && ! in_array($code, ['XX', 'T1'], true) ? $code : null;
    }

    private function paysDepuisIp(string $ip): ?string
    {
        // IP locale / privée (développement) : pas de détection possible.
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