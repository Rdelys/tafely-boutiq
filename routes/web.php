<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Page d'accueil (marketing) — redirige automatiquement vers le
// dashboard si l'utilisateur a déjà une session/remember active.
Route::get('/', [HomeController::class, 'index'])->name('home');
