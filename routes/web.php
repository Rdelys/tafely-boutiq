<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\ProduitController;
use Illuminate\Support\Facades\Route;

// Page d'accueil (marketing) — redirige automatiquement vers le
// dashboard si l'utilisateur a déjà une session/remember active.
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentification par code OTP envoyé par email (connexion ET inscription,
// le compte est créé automatiquement à la vérification s'il n'existait pas).
Route::post('/auth/otp', [AuthController::class, 'sendOtp'])->name('auth.otp.send');
Route::post('/auth/verify', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Produits
    Route::get('/produits', [ProduitController::class, 'index'])->name('produits');
    Route::get('/produits/creer', [ProduitController::class, 'create'])->name('produits.create');
    Route::post('/produits', [ProduitController::class, 'store'])->name('produits.store');
    Route::get('/produits/{produit}/modifier', [ProduitController::class, 'edit'])->name('produits.edit');
    Route::put('/produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');
    Route::delete('/produits/{produit}', [ProduitController::class, 'destroy'])->name('produits.destroy');

    // Pages vides pour l'instant — à brancher sur de vrais contrôleurs/modèles plus tard.
    Route::view('/commandes', 'commandes')->name('commandes');
    Route::view('/notifications', 'notifications')->name('notifications');
    Route::view('/abonnement', 'abonnement')->name('abonnement');

    Route::get('/boutique', [BoutiqueController::class, 'edit'])->name('boutique');
    Route::put('/boutique', [BoutiqueController::class, 'update'])->name('boutique.update');

    Route::get('/parametres', [ParametresController::class, 'edit'])->name('parametres');
    Route::put('/parametres', [ParametresController::class, 'update'])->name('parametres.update');
});