<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La commande devient un "panier" : un en-tête (client, mode, total)
        // + plusieurs lignes de produits (comme une vraie facture).
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
            $table->dropColumn(['produit_id', 'prix_unitaire']);
            $table->string('numero')->nullable()->unique()->after('id');
            $table->unsignedBigInteger('sous_total')->default(0)->after('adresse_livraison');
        });

        Schema::create('commande_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nom_produit'); // photo du nom au moment de la commande
            $table->unsignedBigInteger('prix_unitaire');
            $table->unsignedInteger('quantite');
            $table->unsignedBigInteger('sous_total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_lignes');

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['numero', 'sous_total']);
            $table->foreignId('produit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('prix_unitaire')->default(0);
        });
    }
};