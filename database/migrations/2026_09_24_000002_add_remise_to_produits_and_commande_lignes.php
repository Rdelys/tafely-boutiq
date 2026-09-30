<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            // pourcentage | montant (null = aucune remise)
            $table->string('remise_type', 20)->nullable()->after('prix');
            $table->unsignedInteger('remise_valeur')->nullable()->after('remise_type');
        });

        // Prix avant remise conservé sur chaque ligne de commande
        // (sert à l'étape 7B pour afficher le prix barré sur les factures).
        Schema::table('commande_lignes', function (Blueprint $table) {
            $table->unsignedInteger('prix_initial')->nullable()->after('prix_unitaire');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['remise_type', 'remise_valeur']);
        });

        Schema::table('commande_lignes', function (Blueprint $table) {
            $table->dropColumn('prix_initial');
        });
    }
};