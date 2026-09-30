<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('contexte'); // marchand | admin
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['email', 'contexte', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_request_logs');
    }
};