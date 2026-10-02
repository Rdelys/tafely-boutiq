<?php

use App\Http\Controllers\Api\BoutiqueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API publique (lecture seule) — application mobile
|--------------------------------------------------------------------------
|
| Laravel ajoute automatiquement le préfixe /api : les URL sont donc
| /api/v1/boutiques, /api/v1/categories, etc.
|
*/

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

    Route::get('/categories', [BoutiqueController::class, 'categories']);

    Route::get('/boutiques', [BoutiqueController::class, 'index']);

    // Doit rester AVANT /boutiques/{identifiant}, sinon "resoudre" serait pris pour un identifiant.
    Route::get('/boutiques/resoudre', [BoutiqueController::class, 'resoudre']);

    Route::get('/boutiques/{identifiant}', [BoutiqueController::class, 'show']);
});