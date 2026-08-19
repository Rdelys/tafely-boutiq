<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Étape 1 : envoi du code OTP par email (sert à la fois pour
     * la connexion et l'inscription : si l'utilisateur n'existe pas,
     * il sera créé automatiquement à la vérification du code).
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::where('email', $request->email)->delete();

        Otp::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($request->email)->send(new OtpMail($code));

        return response()->json([
            'message' => 'Un code de vérification a été envoyé à votre adresse email.',
        ]);
    }

    /**
     * Étape 2 : vérification du code, création du compte si besoin,
     * puis connexion avec "remember me" pour rester connecté au-delà
     * de la session (30 minutes d'inactivité).
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $otp = Otp::where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'Code invalide ou expiré.',
            ], 422);
        }

        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['status' => 'free']
        );

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $otp->delete();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
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
