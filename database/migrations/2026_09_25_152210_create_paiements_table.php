<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // abonnement | pack_produits
            $table->string('reference')->unique();
            $table->unsignedInteger('montant');
            $table->string('statut')->default('en_attente'); // en_attente | paye | echoue
            $table->string('papi_transaction_id')->nullable();
            $table->string('papi_payment_method')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('paye_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};