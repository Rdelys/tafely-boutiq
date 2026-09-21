<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('boutique_theme')->nullable();
            $table->string('boutique_couleur')->nullable();
            $table->string('boutique_couleur_perso', 7)->nullable()->after('boutique_couleur');
            $table->string('boutique_description', 300)->nullable()->after('boutique_couleur_perso');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'boutique_theme',
                'boutique_couleur',
                'boutique_couleur_perso',
                'boutique_description',
            ]);
        });
    }
};