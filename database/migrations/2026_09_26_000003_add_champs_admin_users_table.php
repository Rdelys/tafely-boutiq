<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Prolongation manuelle d'essai (geste commercial) — remplace la
            // date de fin d'essai calculée si elle est postérieure.
            $table->date('essai_jusquau')->nullable()->after('abonnement_expire_le');

            // Limite de produits forcée par l'admin, ignore le calcul par plan.
            $table->unsignedInteger('limite_produits_personnalisee')->nullable()->after('limite_produits_bonus');

            // Suspension (abus, litige...).
            $table->boolean('suspendu')->default(false)->after('status');
            $table->string('suspendu_raison')->nullable()->after('suspendu');
            $table->timestamp('suspendu_le')->nullable()->after('suspendu_raison');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['essai_jusquau', 'limite_produits_personnalisee', 'suspendu', 'suspendu_raison', 'suspendu_le']);
        });
    }
};