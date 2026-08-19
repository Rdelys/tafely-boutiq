<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoPricingService
{
    /**
     * Retourne le prix adapté selon le pays détecté depuis l'IP.
     * Madagascar => Ariary, sinon => Euro.
     */
    public function getPrice(?string $ip): array
    {
        $country = $this->resolveCountry($ip);

        if ($country === 'MG') {
            return [
                'amount' => 20000,
                'currency' => 'Ar',
                'formatted' => '20 000 Ar',
            ];
        }

        return [
            'amount' => 6,
            'currency' => '€',
            'formatted' => '6 €',
        ];
    }

    protected function resolveCountry(?string $ip): ?string
    {
        if (! $ip || in_array($ip, ['127.0.0.1', '::1']) || app()->environment('local')) {
            // Par défaut en local / dev : tarif Madagascar
            return 'MG';
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'countryCode',
            ]);

            if ($response->successful()) {
                return $response->json('countryCode');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }
}
