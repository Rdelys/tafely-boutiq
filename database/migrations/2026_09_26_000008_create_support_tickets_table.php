<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sujet');
            $table->string('statut')->default('ouvert'); // ouvert | ferme

            // Pour les badges de notification de chaque côté.
            $table->boolean('nouveau_pour_admin')->default(true);
            $table->boolean('nouveau_pour_marchand')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};