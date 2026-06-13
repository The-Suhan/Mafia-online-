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
            $table->string('nickname')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar_url')->nullable();

            $table->unsignedInteger('xp')->default(0);
            $table->enum('rank', ['rookie', 'novice', 'elite', 'pro', 'master', 'legend'])->default('rookie');

            $table->unsignedInteger('total_played')->default(0);
            $table->unsignedInteger('total_wins')->default(0);
            $table->unsignedInteger('total_losses')->default(0);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
