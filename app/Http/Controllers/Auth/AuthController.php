<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Étape 1 : l'utilisateur saisit son email (connexion OU inscription,
     * le flux est identique — le compte est créé automatiquement à la
     * vérification du code s'il n'existe pas encore).
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));

        // On invalide tout code précédent encore actif pour cet email.
        Otp::where('email', $email)->delete();

        $code = (string) random_int(100000, 999999);

        Otp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $isNewAccount = ! User::where('email', $email)->exists();

        (new AnonymousNotifiable)
            ->route('mail', $email)
            ->notify(new OtpCodeNotification($code));

        return response()->json([
            'message' => 'Code envoyé par email.',
            'is_new_account' => $isNewAccount,
        ]);
    }

    /**
     * Étape 2 : vérification du code. Connecte l'utilisateur existant,
     * ou crée son compte (status = "free") s'il n'existait pas encore,
     * puis redirige vers le dashboard.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $code = $request->input('code');

        $otp = Otp::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $otp->delete();

        $user = User::firstOrCreate(
            ['email' => $email],
            ['status' => 'free']
        );

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Connecté.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}