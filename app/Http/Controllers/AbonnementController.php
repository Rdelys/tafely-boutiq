<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Notifications\AbonnementActiveNotification;
use App\Services\PapiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AbonnementController extends Controller
{
    private const PRIX_ABONNEMENT = 20000;
    private const PRIX_PACK_PRODUITS = 5000;

    public function index(): View
    {
        return view('abonnement');
    }

    public function souscrire(): RedirectResponse
    {
        return $this->demarrerPaiement(
            'abonnement',
            self::PRIX_ABONNEMENT,
            'Abonnement Tafely — 1 mois'
        );
    }

    public function acheterPack(): RedirectResponse
    {
        return $this->demarrerPaiement(
            'pack_produits',
            self::PRIX_PACK_PRODUITS,
            'Pack +10 produits Tafely'
        );
    }

    private function demarrerPaiement(
        string $type,
        int $montant,
        string $description
    ): RedirectResponse {
        $user = Auth::user();

        $prefixe = $type === 'abonnement' ? 'ABN' : 'PCK';

        $reference = $prefixe
            . '-'
            . now()->format('ymd')
            . '-'
            . Str::upper(Str::random(6));

        $paiement = Paiement::create([
            'user_id' => $user->id,
            'type' => $type,
            'reference' => $reference,
            'montant' => $montant,
            'statut' => 'en_attente',
        ]);

        try {
            $lien = app(PapiService::class)->creerLienPaiement([
                'montant' => $montant,

                'reference' => $paiement->reference,

                'clientName' => trim(
                    ($user->prenom ?? '') . ' ' . ($user->nom ?? '')
                ) ?: ($user->nom_boutique ?? 'Client'),

                'description' => $description,

                'clientEmail' =>
                    $user->email_notification ?: $user->email,

                'successUrl' => route(
                    'abonnement.paiement.succes',
                    $paiement->reference
                ),

                'failureUrl' => route(
                    'abonnement.paiement.echec',
                    $paiement->reference
                ),

                'callbackUrl' => route(
                    'abonnement.paiement.callback',
                    $paiement->reference
                ),

                'validDuration' => 24,

                'isTestMode' => false,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Papi — échec de création du lien de paiement',
                [
                    'reference' => $paiement->reference,
                    'erreur' => $e->getMessage(),
                ]
            );

            $paiement->update([
                'statut' => 'echoue',
            ]);

            return redirect()
                ->route('abonnement')
                ->with(
                    'erreur',
                    "Le paiement n'a pas pu être initié pour le moment. Réessayez dans quelques instants."
                );
        }

        return redirect()->away($lien);
    }

    /**
     * Callback envoyé par Papi.
     */
    public function callback(Request $request, string $reference)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Vérification de la signature Papi
        |--------------------------------------------------------------------------
        */

        $secret = config('services.papi.webhook_secret');

        if (empty($secret)) {
            Log::error('Papi callback : secret webhook non configuré.');

            return response()->json([
                'message' => 'Webhook non configuré.',
            ], 500);
        }

        $signatureHeader = $request->header('X-Papi-Signature');

        if (empty($signatureHeader)) {
            Log::warning(
                'Papi callback sans signature',
                [
                    'reference' => $reference,
                ]
            );

            return response()->json([
                'message' => 'Signature manquante.',
            ], 401);
        }

        $timestamp = null;
        $signature = null;

        foreach (explode(',', $signatureHeader) as $part) {
            $part = trim($part);

            if (str_starts_with($part, 't=')) {
                $timestamp = substr($part, 2);
            }

            if (str_starts_with($part, 'v1=')) {
                $signature = substr($part, 3);
            }
        }

        if (
            ! $timestamp ||
            ! $signature ||
            ! ctype_digit($timestamp)
        ) {
            return response()->json([
                'message' => 'Signature invalide.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Protection contre le rejeu
        |--------------------------------------------------------------------------
        */

        if (abs(time() - (int) $timestamp) > 300) {
            return response()->json([
                'message' => 'Signature expirée.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Calcul de la signature
        |--------------------------------------------------------------------------
        */

        $rawBody = $request->getContent();

        $signedPayload = $timestamp . '.' . $rawBody;

        $expectedSignature = hash_hmac(
            'sha256',
            $signedPayload,
            $secret
        );

        if (! hash_equals($expectedSignature, $signature)) {

            Log::warning(
                'Papi callback : signature invalide',
                [
                    'reference' => $reference,
                ]
            );

            return response()->json([
                'message' => 'Signature invalide.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Récupération du paiement
        |--------------------------------------------------------------------------
        */

        $paiement = Paiement::where(
            'reference',
            $reference
        )->first();

        if (! $paiement) {
            Log::warning(
                'Papi callback : paiement introuvable',
                [
                    'reference' => $reference,
                ]
            );

            return response()->json([
                'message' => 'Paiement introuvable.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Éviter de traiter deux fois le même paiement
        |--------------------------------------------------------------------------
        */

        if ($paiement->statut !== 'en_attente') {
            return response()->json([
                'message' => 'Déjà traité.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Vérification de la référence
        |--------------------------------------------------------------------------
        */

        $merchantReference = $request->input(
            'merchantPaymentReference'
        );

        if ($merchantReference !== $paiement->reference) {

            Log::warning(
                'Papi callback : mauvaise référence',
                [
                    'reference_attendue' => $paiement->reference,
                    'reference_recue' => $merchantReference,
                ]
            );

            return response()->json([
                'message' => 'Référence invalide.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Vérification du montant
        |--------------------------------------------------------------------------
        */

        $montantRecu = (int) round(
            (float) $request->input('amount')
        );

        if ($montantRecu !== (int) $paiement->montant) {

            Log::warning(
                'Papi callback : montant incorrect',
                [
                    'reference' => $paiement->reference,
                    'montant_attendu' => $paiement->montant,
                    'montant_recu' => $montantRecu,
                ]
            );

            $paiement->update([
                'statut' => 'echoue',
                'meta' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Montant incorrect.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Vérification du statut Papi
        |--------------------------------------------------------------------------
        */

        $paymentStatus = strtoupper(
            (string) $request->input('paymentStatus')
        );

        /*
        |--------------------------------------------------------------------------
        | 9. Enregistrer les informations Papi
        |--------------------------------------------------------------------------
        */

        $paiement->papi_transaction_id =
            $request->input('papiPaymentReference')
            ?? $request->input('paymentReference');

        $paiement->papi_payment_method =
            $request->input('paymentMethod');

        $paiement->meta = $request->all();

        /*
        |--------------------------------------------------------------------------
        | 10. Paiement réussi
        |--------------------------------------------------------------------------
        */

        if ($paymentStatus === 'SUCCESS') {

            $paiement->statut = 'paye';
            $paiement->paye_le = now();
            $paiement->save();

            $this->activerAvantage($paiement);

            Log::info(
                'Papi — paiement confirmé',
                [
                    'reference' => $paiement->reference,
                    'montant' => $paiement->montant,
                    'type' => $paiement->type,
                ]
            );

        } else {

            $paiement->statut = 'echoue';
            $paiement->save();

            Log::warning(
                'Papi — paiement non réussi',
                [
                    'reference' => $paiement->reference,
                    'paymentStatus' => $paymentStatus,
                ]
            );
        }

        return response()->json([
            'message' => 'OK',
        ], 200);
    }

    private function activerAvantage(Paiement $paiement): void
    {
        $user = $paiement->user;

        if ($paiement->type === 'abonnement') {

            $depart = $user->abonnementActif()
                ? $user->abonnement_expire_le
                : now();

            $user->status = 'active';

            $user->abonnement_expire_le =
                $depart->copy()->addMonth();

            $user->save();

        } elseif ($paiement->type === 'pack_produits') {

            $user->limite_produits_bonus += 10;

            $user->save();
        }

        $user->notify(
            new AbonnementActiveNotification($paiement)
        );
    }

    public function succes(string $reference): View
    {
        return view(
            'abonnement-resultat',
            [
                'paiement' => Paiement::where(
                    'reference',
                    $reference
                )->firstOrFail(),
            ]
        );
    }

    public function echec(string $reference): View
    {
        return view(
            'abonnement-resultat',
            [
                'paiement' => Paiement::where(
                    'reference',
                    $reference
                )->firstOrFail(),
            ]
        );
    }
}