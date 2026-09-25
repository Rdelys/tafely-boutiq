<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PapiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.papi.base_url', 'https://app.papi.mg'),
            '/'
        );

        $this->apiKey = (string) config('services.papi.api_key');
    }

    public function creerLienPaiement(array $donnees): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'Clé API Papi non configurée (PAPI_API_KEY).'
            );
        }

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $this->apiKey,
            ])
            ->post(
                $this->baseUrl . '/engine/api/payment-links',
                [
                    'amount' => $donnees['montant'],

                    'clientName' => $donnees['clientName'],

                    'reference' => $donnees['reference'],

                    'description' => $donnees['description'],

                    'successUrl' => $donnees['successUrl'],

                    'failureUrl' => $donnees['failureUrl'],

                    'notificationUrl' => $donnees['callbackUrl'],

                    'validDuration' =>
                        $donnees['validDuration'] ?? 24,

                    'payerEmail' =>
                        $donnees['clientEmail'] ?? null,

                    'payerPhone' =>
                        $donnees['clientPhone'] ?? null,

                    'isTestMode' =>
                        $donnees['isTestMode'] ?? false,
                ]
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Papi a refusé la demande de paiement : '
                . $response->status()
                . ' - '
                . $response->body()
            );
        }

        $json = $response->json();

        $paymentLink = data_get(
            $json,
            'data.paymentLink'
        );

        if (! empty($paymentLink)) {
            return $paymentLink;
        }

        throw new RuntimeException(
            'Réponse Papi inattendue : '
            . $response->body()
        );
    }
}