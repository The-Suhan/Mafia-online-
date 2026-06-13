<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number')->default(1);
            $table->enum('phase', [
                'role_assignment',
                'night',
                'day_announcement',
                'day_vote',
                'finished',
            ])->default('role_assignment');
            $table->unsignedTinyInteger('night_actions_submitted')->default(0);
            $table->timestamp('phase_ends_at')->nullable();
            $table->enum('winner', ['mafia', 'villagers'])->nullable();
            $table->unsignedTinyInteger('player_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'winner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
