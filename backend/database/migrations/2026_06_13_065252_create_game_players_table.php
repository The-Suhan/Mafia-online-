<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', [
                // Temel roller
                'mafia',
                'villager',
                'sheriff',
                'doctor',
                // Mafia takımı
                'godfather',
                'silencer',
                // Köy takımı
                'vigilante',
                'mayor',
                'bodyguard',
                'spy',
                'jester',
                'executioner',
            ]);
            $table->boolean('is_alive')->default(true);
            $table->unsignedTinyInteger('self_heal_used')->default(0);
            $table->boolean('is_winner')->nullable();
            $table->unsignedSmallInteger('xp_earned')->default(0);
            $table->timestamps();

            $table->unique(['session_id', 'user_id']);
            $table->index(['session_id', 'is_alive']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};
