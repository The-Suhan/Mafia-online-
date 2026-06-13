<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('max_players')->default(8);
            $table->unsignedTinyInteger('current_players')->default(0);
            $table->enum('status', ['waiting', 'playing', 'closed'])->default('waiting');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
