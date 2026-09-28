<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->boolean('bloque')->default(false)->after('image');
            $table->string('bloque_raison')->nullable()->after('bloque');
            $table->timestamp('bloque_le')->nullable()->after('bloque_raison');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['bloque', 'bloque_raison', 'bloque_le']);
        });
    }
};