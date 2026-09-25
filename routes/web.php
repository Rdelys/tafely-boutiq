<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\VenteBoutiqueController;
use App\Http\Controllers\VitrineController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/b/{identifiant}', [VitrineController::class, 'show'])
    ->name('vitrine');

Route::post('/b/{identifiant}/commander', [CommandeController::class, 'store'])
    ->name('commandes.store');

Route::get('/commande/{numero}/recu', [CommandeController::class, 'recu'])
    ->name('commandes.recu');


/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
*/

Route::post('/auth/otp', [AuthController::class, 'sendOtp'])
    ->name('auth.otp.send');

Route::post('/auth/verify', [AuthController::class, 'verifyOtp'])
    ->name('auth.otp.verify');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| UTILISATEUR CONNECTÉ
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Produits
    |--------------------------------------------------------------------------
    */

    Route::get('/produits', [ProduitController::class, 'index'])
        ->name('produits');

    Route::get('/produits/{produit}/modifier', [ProduitController::class, 'edit'])
        ->name('produits.edit');

    Route::put('/produits/{produit}', [ProduitController::class, 'update'])
        ->name('produits.update');

    Route::delete('/produits/{produit}', [ProduitController::class, 'destroy'])
        ->name('produits.destroy');


    /*
    |--------------------------------------------------------------------------
    | Commandes marchand
    |--------------------------------------------------------------------------
    */

    Route::get('/commandes', [CommandeController::class, 'index'])
        ->name('commandes');

    Route::get('/commandes/{commande}/facture', [CommandeController::class, 'facture'])
        ->name('commandes.facture');

    Route::put('/commandes/{commande}/statut', [CommandeController::class, 'updateStatut'])
        ->name('commandes.statut');


    /*
    |--------------------------------------------------------------------------
    | Vente en boutique
    |--------------------------------------------------------------------------
    */

    Route::get('/ventes/nouvelle', [VenteBoutiqueController::class, 'create'])
        ->name('ventes.create');

    Route::post('/ventes', [VenteBoutiqueController::class, 'store'])
        ->name('ventes.store');


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::view('/notifications', 'notifications')
        ->name('notifications');


    /*
    |--------------------------------------------------------------------------
    | Abonnement
    |--------------------------------------------------------------------------
    */

    Route::get('/abonnement', [AbonnementController::class, 'index'])
        ->name('abonnement');

    Route::post('/abonnement/souscrire', [AbonnementController::class, 'souscrire'])
        ->name('abonnement.souscrire');

    Route::post('/abonnement/pack', [AbonnementController::class, 'acheterPack'])
        ->name('abonnement.pack');


    /*
    |--------------------------------------------------------------------------
    | Boutique
    |--------------------------------------------------------------------------
    */

    Route::get('/boutique', [BoutiqueController::class, 'edit'])
        ->name('boutique');

    Route::put('/boutique', [BoutiqueController::class, 'update'])
        ->name('boutique.update');


    /*
    |--------------------------------------------------------------------------
    | Paramètres
    |--------------------------------------------------------------------------
    */

    Route::get('/parametres', [ParametresController::class, 'edit'])
        ->name('parametres');

    Route::put('/parametres', [ParametresController::class, 'update'])
        ->name('parametres.update');
});


/*
|--------------------------------------------------------------------------
| FONCTIONNALITÉS NÉCESSITANT UN ABONNEMENT ACTIF
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'abonnement.actif'])->group(function () {

    Route::get('/produits/creer', [ProduitController::class, 'create'])
        ->name('produits.create');

    Route::post('/produits', [ProduitController::class, 'store'])
        ->name('produits.store');

    Route::get('/ventes/nouvelle', [VenteBoutiqueController::class, 'create'])
        ->name('ventes.create');

    Route::post('/ventes', [VenteBoutiqueController::class, 'store'])
        ->name('ventes.store');
});


/*
|--------------------------------------------------------------------------
| RETOUR PAIEMENT PAPI
|--------------------------------------------------------------------------
|
| Ces routes doivent rester accessibles sans authentification.
| Papi doit pouvoir appeler le callback serveur.
|
*/

Route::get(
    '/abonnement/paiement/{reference}/succes',
    [AbonnementController::class, 'succes']
)->name('abonnement.paiement.succes');

Route::get(
    '/abonnement/paiement/{reference}/echec',
    [AbonnementController::class, 'echec']
)->name('abonnement.paiement.echec');

Route::post(
    '/abonnement/paiement/{reference}/callback',
    [AbonnementController::class, 'callback']
)->name('abonnement.paiement.callback');

Route::view('/aide', 'aide')->name('aide');
Route::view('/confidentialite', 'confidentialite')->name('confidentialite');
Route::view('/contact', 'contact')->name('contact');