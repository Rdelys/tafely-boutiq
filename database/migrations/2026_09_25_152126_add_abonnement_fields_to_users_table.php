<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('abonnement_expire_le')->nullable()->after('status');
            $table->unsignedInteger('limite_produits_bonus')->default(0)->after('abonnement_expire_le');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['abonnement_expire_le', 'limite_produits_bonus']);
        });
    }
};