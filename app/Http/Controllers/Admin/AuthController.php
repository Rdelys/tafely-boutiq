<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminOtp;
use App\Notifications\AdminOtpCodeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Seule adresse autorisée à accéder au panneau admin.
     */
    private const EMAIL_AUTORISE = 'contact@tafely-gr.com';

        /**
     * Étape 1 : demande de code. Seule l'adresse admin autorisée peut
     * recevoir un code — toute autre adresse est rejetée explicitement.
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

        \App\Models\OtpRequestLog::create([
            'email' => $email,
            'contexte' => 'admin',
            'ip' => $request->ip(),
        ]);

        // Anti-brute-force : 5 demandes / 5 minutes par IP.
        $cle = 'admin-otp:'.$request->ip();
        
        if (RateLimiter::tooManyAttempts($cle, 5)) {
            return response()->json([
                'message' => 'Trop de tentatives. Réessayez dans quelques minutes.',
            ], 429);
        }

        RateLimiter::hit($cle, 300);

        // Adresse non autorisée : rejet direct et explicite.
        if ($email !== self::EMAIL_AUTORISE) {
            return response()->json([
                'message' => 'Cette adresse n\'est pas autorisée à accéder au panneau d\'administration.',
            ], 403);
        }

        AdminOtp::where('email', $email)->delete();

        $code = (string) random_int(100000, 999999);

        AdminOtp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        (new AnonymousNotifiable)
            ->route('mail', $email)
            ->notify(new AdminOtpCodeNotification($code));

        return response()->json([
            'message' => 'Code envoyé par email.',
        ]);
    }

    /**
     * Étape 2 : vérification du code et connexion sur le guard "admin".
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

        if ($email !== self::EMAIL_AUTORISE) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $otp = AdminOtp::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $otp->delete();

        $admin = Admin::firstOrCreate(['email' => $email]);

        if (! $admin->email_verified_at) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::guard('admin')->login($admin, true);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Connecté.',
            'redirect' => route('admin.dashboard'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}