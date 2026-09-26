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
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

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

Route::middleware(['auth', 'compte.actif'])->group(function () {
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

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])
    ->name('notifications');

    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SupportController::class, 'index'])->name('index');
        Route::get('/nouveau', [\App\Http\Controllers\SupportController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SupportController::class, 'store'])->name('store');
        Route::get('/{ticket}', [\App\Http\Controllers\SupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/repondre', [\App\Http\Controllers\SupportController::class, 'repondre'])->name('repondre');
    });

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

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::view('/connexion', 'admin.login')->name('login');
        Route::post('/connexion/otp', [AdminAuthController::class, 'sendOtp'])->name('otp.send');
        Route::post('/connexion/verifier', [AdminAuthController::class, 'verifyOtp'])->name('otp.verify');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/deconnexion', [AdminAuthController::class, 'logout'])->name('logout');

        Route::prefix('marchands')->name('marchands.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MarchandController::class, 'index'])->name('index');
            Route::get('/{utilisateur}', [\App\Http\Controllers\Admin\MarchandController::class, 'show'])->name('show');
            Route::post('/{utilisateur}/suspendre', [\App\Http\Controllers\Admin\MarchandActionController::class, 'suspendre'])->name('suspendre');
            Route::post('/{utilisateur}/reactiver', [\App\Http\Controllers\Admin\MarchandActionController::class, 'reactiver'])->name('reactiver');
            Route::post('/{utilisateur}/prolonger-essai', [\App\Http\Controllers\Admin\MarchandActionController::class, 'prolongerEssai'])->name('prolonger-essai');
            Route::post('/{utilisateur}/prolonger-abonnement', [\App\Http\Controllers\Admin\MarchandActionController::class, 'prolongerAbonnement'])->name('prolonger-abonnement');
            Route::put('/{utilisateur}/limite-produits', [\App\Http\Controllers\Admin\MarchandActionController::class, 'definirLimiteProduits'])->name('limite-produits');
        });

        Route::prefix('paiements')->name('paiements.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PaiementController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Admin\PaiementController::class, 'exportCsv'])->name('export');
        });

        Route::prefix('produits')->name('produits.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ProduitController::class, 'index'])->name('index');
            Route::post('/{produit}/bloquer', [\App\Http\Controllers\Admin\ProduitActionController::class, 'bloquer'])->name('bloquer');
            Route::post('/{produit}/reactiver', [\App\Http\Controllers\Admin\ProduitActionController::class, 'reactiver'])->name('reactiver');
            Route::delete('/{produit}', [\App\Http\Controllers\Admin\ProduitActionController::class, 'detruire'])->name('detruire');
        });

        Route::prefix('commandes')->name('commandes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CommandeController::class, 'index'])->name('index');
        });

        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('index');
            Route::get('/{ticket}', [\App\Http\Controllers\Admin\SupportController::class, 'show'])->name('show');
            Route::post('/{ticket}/repondre', [\App\Http\Controllers\Admin\SupportController::class, 'repondre'])->name('repondre');
            Route::post('/{ticket}/fermer', [\App\Http\Controllers\Admin\SupportController::class, 'fermer'])->name('fermer');
            Route::post('/{ticket}/rouvrir', [\App\Http\Controllers\Admin\SupportController::class, 'rouvrir'])->name('rouvrir');
        });

        Route::prefix('communication')->name('communication.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CommunicationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\CommunicationController::class, 'store'])->name('store');
        });

        Route::prefix('securite')->name('securite.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SecuriteController::class, 'index'])->name('index');
        });
    });

    
});