<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // le marchand
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();

            $table->string('nom_client');
            $table->string('telephone_client');

            $table->unsignedInteger('quantite')->default(1);

            // recuperer = le client vient chercher (date + heure) | livrer = livraison (adresse)
            $table->enum('mode', ['recuperer', 'livrer']);
            $table->date('date_recuperation')->nullable();
            $table->string('heure_recuperation')->nullable();
            $table->string('adresse_livraison')->nullable();

            // Photo des prix au moment de la commande (indépendant d'un changement de prix futur)
            $table->unsignedBigInteger('prix_unitaire');
            $table->unsignedBigInteger('prix_livraison')->nullable();
            $table->unsignedBigInteger('total');

            $table->enum('statut', ['a_prendre_en_compte', 'en_cours_de_livraison', 'livree'])
                  ->default('a_prendre_en_compte');

            // Garde-fou : le stock n'est décrémenté qu'une seule fois, au passage à "livrée".
            $table->boolean('stock_decremente')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};