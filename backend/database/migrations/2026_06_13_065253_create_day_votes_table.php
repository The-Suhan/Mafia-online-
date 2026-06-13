<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->foreignId('voter_id')->constrained('users');
            $table->foreignId('target_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['session_id', 'round_number', 'voter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_votes');
    }
};
