<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('character_memory_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->unique()->constrained('characters')->cascadeOnDelete();
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->unsignedBigInteger('total_cash_won')->default(0);
            $table->unsignedInteger('best_time_seconds')->nullable();
            $table->unsignedInteger('best_moves')->nullable();
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_memory_stats');
    }
};
