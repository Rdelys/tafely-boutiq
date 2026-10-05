<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visites', function (Blueprint $table) {
            $table->id();
            $table->string('visiteur', 64)->index();   // empreinte anonyme (IP + navigateur)
            $table->string('chemin');                  // ex : /  ou  /b/ma-boutique
            $table->string('type', 20);                // accueil | vitrine | page
            $table->string('source', 20)->default('Direct'); // Direct, Facebook, WhatsApp...
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visites');
    }
};