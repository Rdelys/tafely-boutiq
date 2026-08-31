<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParametresController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/auth/otp', [AuthController::class, 'sendOtp'])->name('auth.otp.send');
Route::post('/auth/verify', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pages vides pour l'instant — à brancher sur de vrais contrôleurs/modèles plus tard.
    Route::view('/produits', 'produits')->name('produits');
    Route::view('/commandes', 'commandes')->name('commandes');
    Route::view('/boutique', 'boutique')->name('boutique');
    Route::view('/notifications', 'notifications')->name('notifications');
    Route::view('/abonnement', 'abonnement')->name('abonnement');
    Route::get('/parametres', [ParametresController::class, 'edit'])->name('parametres');
    Route::put('/parametres', [ParametresController::class, 'update'])->name('parametres.update');
});