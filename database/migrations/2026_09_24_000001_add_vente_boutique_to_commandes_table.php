<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // 'en_ligne' (vitrine) ou 'boutique' (vente en magasin)
            $table->string('source', 20)->default('en_ligne')->after('numero');

            // especes | mvola | orange_money | autre (utilisé pour les ventes en boutique)
            $table->string('mode_paiement', 20)->nullable()->after('total');

            // Un client de passage peut ne pas donner son nom / téléphone
            $table->string('nom_client')->nullable()->change();
            $table->string('telephone_client', 30)->nullable()->change();

            // Autorise la nouvelle valeur 'sur_place' (en plus de recuperer / livrer)
            $table->string('mode', 20)->default('recuperer')->change();
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['source', 'mode_paiement']);
        });
    }
};