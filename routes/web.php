<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Page d'accueil (marketing) — redirige automatiquement vers le
// dashboard si l'utilisateur a déjà une session/remember active.
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentification par email + OTP (connexion & inscription unifiées)
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Espace connecté
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/boutique', [DashboardController::class, 'boutique'])->name('boutique');
    Route::get('/commande', [DashboardController::class, 'commande'])->name('commande');
    Route::get('/parametres', [DashboardController::class, 'parametres'])->name('parametres');
});
