<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->string('event', 60);
            $table->json('data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['session_id', 'round_number']);
            $table->index(['session_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_logs');
    }
};
