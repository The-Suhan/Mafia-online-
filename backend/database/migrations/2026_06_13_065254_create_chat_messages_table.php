<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('game_sessions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->enum('type', ['lobby', 'day', 'bot', 'system'])->default('lobby');
            $table->unsignedTinyInteger('round_number')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['room_id', 'type', 'created_at']);
            $table->index(['session_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
