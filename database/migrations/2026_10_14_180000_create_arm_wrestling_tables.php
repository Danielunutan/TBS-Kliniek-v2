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
        // 1. Arm wrestling matches (NPC and PvP open challenges)
        if (! Schema::hasTable('arm_wrestle_matches')) {
            Schema::create('arm_wrestle_matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('challenger_id')->constrained('characters')->cascadeOnDelete();
                $table->foreignId('opponent_id')->nullable()->constrained('characters')->nullOnDelete();
                $table->string('type')->default('npc'); // 'npc' or 'pvp'
                $table->string('npc_key')->nullable(); // 'sjonnie', 'karel', 'bennie', 'mike'
                $table->unsignedBigInteger('bet_amount')->default(0);
                $table->foreignId('winner_id')->nullable()->constrained('characters')->nullOnDelete();
                $table->string('status')->default('completed'); // 'open', 'completed', 'cancelled'
                $table->unsignedInteger('xp_earned')->default(0);
                $table->integer('respect_earned')->default(0);
                $table->unsignedInteger('challenger_clicks')->default(0);
                $table->unsignedInteger('match_duration_ms')->default(0);
                $table->timestamps();

                $table->index(['challenger_id', 'created_at']);
                $table->index(['status', 'type', 'created_at']);
            });
        }

        // 2. Arm wrestling stats per character
        if (! Schema::hasTable('character_arm_wrestle_stats')) {
            Schema::create('character_arm_wrestle_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->unique()->constrained('characters')->cascadeOnDelete();
                $table->unsignedInteger('wins')->default(0);
                $table->unsignedInteger('losses')->default(0);
                $table->unsignedInteger('current_streak')->default(0);
                $table->unsignedInteger('best_streak')->default(0);
                $table->bigInteger('total_profit')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_arm_wrestle_stats');
        Schema::dropIfExists('arm_wrestle_matches');
    }
};
