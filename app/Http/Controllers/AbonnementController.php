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

    /**
     * Souscription à l'abonnement mensuel.
     */
    public function souscrire(): RedirectResponse
    {
        return $this->demarrerPaiement(
            'abonnement',
            self::PRIX_ABONNEMENT,
            'Abonnement Tafely — 1 mois'
        );
    }

    /**
     * Achat d'un pack de 10 produits.
     */
    public function acheterPack(): RedirectResponse
    {
        return $this->demarrerPaiement(
            'pack_produits',
            self::PRIX_PACK_PRODUITS,
            'Pack +10 produits Tafely'
        );
    }

    /**
     * Création du paiement Papi.
     */
    private function demarrerPaiement(
        string $type,
        int $montant,
        string $description
    ): RedirectResponse {
        $user = Auth::user();

        $prefixe = $type === 'abonnement'
            ? 'ABN'
            : 'PCK';

        $reference = $prefixe
            . '-'
            . now()->format('ymd')
            . '-'
            . Str::upper(Str::random(6));

        /*
        |--------------------------------------------------------------------------
        | Création du paiement local
        |--------------------------------------------------------------------------
        */

        $paiement = Paiement::create([
            'user_id' => $user->id,
            'type' => $type,
            'reference' => $reference,
            'montant' => $montant,
            'statut' => 'en_attente',
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | Création du lien Papi
            |--------------------------------------------------------------------------
            */

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

                /*
                 * Production.
                 */
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

        /*
        |--------------------------------------------------------------------------
        | Redirection vers Papi
        |--------------------------------------------------------------------------
        */

        return redirect()->away($lien);
    }

    /**
     * Callback envoyé par Papi.
     *
     * IMPORTANT :
     *
     * Un même lien Papi peut recevoir plusieurs tentatives :
     *
     * FAILED
     * FAILED
     * FAILED
     * SUCCESS
     *
     * Un FAILED ne doit donc PAS empêcher une future notification SUCCESS.
     */
    public function callback(Request $request, string $reference)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Vérification du secret webhook
        |--------------------------------------------------------------------------
        */

        $secret = config('services.papi.webhook_secret');

        if (empty($secret)) {

            Log::error(
                'Papi callback : secret webhook non configuré.'
            );

            return response()->json([
                'message' => 'Webhook non configuré.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Récupération de la signature
        |--------------------------------------------------------------------------
        */

        $signatureHeader = $request->header(
            'X-Papi-Signature'
        );

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

        foreach (
            explode(',', $signatureHeader)
            as $part
        ) {

            $part = trim($part);

            if (str_starts_with($part, 't=')) {
                $timestamp = substr($part, 2);
            }

            if (str_starts_with($part, 'v1=')) {
                $signature = substr($part, 3);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Vérification du format de signature
        |--------------------------------------------------------------------------
        */

        if (
            ! $timestamp ||
            ! $signature ||
            ! ctype_digit($timestamp)
        ) {

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
        | 4. Protection contre le rejeu
        |--------------------------------------------------------------------------
        */

        if (
            abs(
                time() - (int) $timestamp
            ) > 300
        ) {

            Log::warning(
                'Papi callback : signature expirée',
                [
                    'reference' => $reference,
                ]
            );

            return response()->json([
                'message' => 'Signature expirée.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Vérification HMAC
        |--------------------------------------------------------------------------
        */

        $rawBody = $request->getContent();

        $signedPayload =
            $timestamp . '.' . $rawBody;

        $expectedSignature = hash_hmac(
            'sha256',
            $signedPayload,
            $secret
        );

        if (
            ! hash_equals(
                $expectedSignature,
                $signature
            )
        ) {

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
        | 6. Recherche du paiement
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
        | 7. Vérification de la référence marchand
        |--------------------------------------------------------------------------
        */

        $merchantReference =
            $request->input(
                'merchantPaymentReference'
            );

        if (
            $merchantReference !==
            $paiement->reference
        ) {

            Log::warning(
                'Papi callback : mauvaise référence',
                [
                    'reference_attendue' =>
                        $paiement->reference,

                    'reference_recue' =>
                        $merchantReference,
                ]
            );

            return response()->json([
                'message' => 'Référence invalide.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Vérification du montant
        |--------------------------------------------------------------------------
        */

        $montantRecu = (int) round(
            (float) $request->input('amount')
        );

        if (
            $montantRecu !==
            (int) $paiement->montant
        ) {

            Log::warning(
                'Papi callback : montant incorrect',
                [
                    'reference' =>
                        $paiement->reference,

                    'montant_attendu' =>
                        $paiement->montant,

                    'montant_recu' =>
                        $montantRecu,
                ]
            );

            /*
             * On ne transforme pas ici un paiement déjà
             * confirmé en échec.
             */
            if ($paiement->statut !== 'paye') {

                $paiement->statut = 'echoue';
                $paiement->meta = $request->all();
                $paiement->save();
            }

            return response()->json([
                'message' => 'Montant incorrect.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Récupération du statut Papi
        |--------------------------------------------------------------------------
        */

        $paymentStatus = strtoupper(
            (string) $request->input(
                'paymentStatus'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | 10. Enregistrement de CHAQUE notification
        |--------------------------------------------------------------------------
        */

        $paiement->papi_transaction_id =
            $request->input(
                'papiPaymentReference'
            )
            ?? $request->input(
                'paymentReference'
            )
            ?? $paiement->papi_transaction_id;

        $paiement->papi_payment_method =
            $request->input(
                'paymentMethod'
            )
            ?? $paiement->papi_payment_method;

        $paiement->meta =
            $request->all();

        /*
        |--------------------------------------------------------------------------
        | 11. Paiement SUCCESS
        |--------------------------------------------------------------------------
        */

        if ($paymentStatus === 'SUCCESS') {

            /*
            |--------------------------------------------------------------------------
            | SUCCESS déjà traité
            |--------------------------------------------------------------------------
            |
            | On ne crédite jamais deux fois.
            |
            */

            if ($paiement->statut === 'paye') {

                $paiement->save();

                Log::info(
                    'Papi — SUCCESS déjà traité',
                    [
                        'reference' =>
                            $paiement->reference,

                        'transaction' =>
                            $paiement->papi_transaction_id,
                    ]
                );

                return response()->json([
                    'message' =>
                        'SUCCESS déjà traité.',
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Première confirmation SUCCESS
            |--------------------------------------------------------------------------
            */

            $paiement->statut = 'paye';
            $paiement->paye_le = now();

            $paiement->save();

            /*
            |--------------------------------------------------------------------------
            | Activation de l'avantage
            |--------------------------------------------------------------------------
            */

            $this->activerAvantage(
                $paiement
            );

            Log::info(
                'Papi — paiement confirmé',
                [
                    'reference' =>
                        $paiement->reference,

                    'montant' =>
                        $paiement->montant,

                    'type' =>
                        $paiement->type,

                    'paymentMethod' =>
                        $paiement->papi_payment_method,

                    'transaction' =>
                        $paiement->papi_transaction_id,
                ]
            );

            return response()->json([
                'message' =>
                    'Paiement confirmé.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 12. Paiement FAILED
        |--------------------------------------------------------------------------
        |
        | IMPORTANT :
        |
        | FAILED = cette tentative a échoué.
        |
        | Le client peut immédiatement refaire une tentative
        | depuis la même page Papi.
        |
        */

        if ($paymentStatus === 'FAILED') {

            /*
            |--------------------------------------------------------------------------
            | Un paiement déjà réussi reste réussi.
            |--------------------------------------------------------------------------
            */

            if ($paiement->statut !== 'paye') {

                $paiement->statut = 'echoue';
            }

            $paiement->save();

            Log::warning(
                'Papi — tentative de paiement échouée',
                [
                    'reference' =>
                        $paiement->reference,

                    'paymentStatus' =>
                        $paymentStatus,

                    'message' =>
                        $request->input('message'),

                    'paymentMethod' =>
                        $request->input('paymentMethod'),

                    'transaction' =>
                        $paiement->papi_transaction_id,
                ]
            );

            return response()->json([
                'message' =>
                    'Tentative échouée enregistrée.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 13. Autres statuts
        |--------------------------------------------------------------------------
        |
        | Exemple :
        |
        | PENDING
        | PROCESSING
        |
        | On conserve la notification sans considérer
        | le paiement comme terminé.
        |
        */

        $paiement->save();

        Log::info(
            'Papi — statut intermédiaire',
            [
                'reference' =>
                    $paiement->reference,

                'paymentStatus' =>
                    $paymentStatus,

                'message' =>
                    $request->input('message'),

                'paymentMethod' =>
                    $request->input('paymentMethod'),
            ]
        );

        return response()->json([
            'message' =>
                'Notification reçue.',
        ], 200);
    }

    /**
     * Active l'abonnement ou ajoute le pack de produits.
     */
    private function activerAvantage(
        Paiement $paiement
    ): void {

        $user = $paiement->user;

        /*
        |--------------------------------------------------------------------------
        | Abonnement
        |--------------------------------------------------------------------------
        */

        if (
            $paiement->type ===
            'abonnement'
        ) {

            $depart =
                $user->abonnementActif()
                    ? $user->abonnement_expire_le
                    : now();

            $user->status = 'active';

            $user->abonnement_expire_le =
                $depart->copy()->addMonth();

            $user->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Pack produits
        |--------------------------------------------------------------------------
        */

        elseif (
            $paiement->type ===
            'pack_produits'
        ) {

            $user->limite_produits_bonus += 10;

            $user->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Notification utilisateur
        |--------------------------------------------------------------------------
        */

        $user->notify(
            new AbonnementActiveNotification(
                $paiement
            )
        );
    }

    /**
     * Page après retour Papi : succès.
     */
    public function succes(
        string $reference
    ): View {

        return view(
            'abonnement-resultat',
            [
                'paiement' =>
                    Paiement::where(
                        'reference',
                        $reference
                    )->firstOrFail(),
            ]
        );
    }

    /**
     * Page après retour Papi : échec.
     *
     * Cette page peut également afficher l'état
     * réel du paiement après une nouvelle tentative.
     */
    public function echec(
        string $reference
    ): View {

        return view(
            'abonnement-resultat',
            [
                'paiement' =>
                    Paiement::where(
                        'reference',
                        $reference
                    )->firstOrFail(),
            ]
        );
    }
}