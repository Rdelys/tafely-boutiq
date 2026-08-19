<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('pseudo')->unique()->nullable();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('nom_boutique')->nullable();
            $table->string('adresse')->nullable();
            $table->enum('status', ['free', 'test', 'active'])->default('free');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
