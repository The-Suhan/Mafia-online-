<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->foreignId('actor_id')->constrained('users');
            $table->foreignId('target_id')->constrained('users');
            $table->enum('action_type', ['kill', 'heal', 'inspect']);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['session_id', 'round_number', 'actor_id']);
            $table->index(['session_id', 'round_number', 'is_resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_actions');
    }
};
