<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('prix'); // en Ariary
            $table->string('image')->nullable();
            $table->unsignedInteger('stock')->nullable();
            // aucune = livraison non proposée | payante = livraison proposée avec un prix
            $table->enum('livraison', ['aucune', 'payante'])->default('aucune');
            $table->unsignedBigInteger('prix_livraison')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};