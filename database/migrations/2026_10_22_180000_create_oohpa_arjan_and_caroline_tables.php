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
        // 1. OOHPA Arjan 4 op 'n Rij Stats & Tournament state
        if (! Schema::hasTable('character_connect_four_stats')) {
            Schema::create('character_connect_four_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->unique()->constrained('characters')->cascadeOnDelete();
                $table->unsignedInteger('matches_played')->default(0); // 0 to 5 in current tournament
                $table->unsignedInteger('current_player_wins')->default(0);
                $table->unsignedInteger('current_arjan_wins')->default(0);
                $table->unsignedInteger('current_draws')->default(0);
                $table->unsignedInteger('total_tournaments_played')->default(0);
                $table->unsignedInteger('total_tournaments_won')->default(0);
                $table->unsignedInteger('total_games_won')->default(0);
                $table->unsignedBigInteger('total_cash_won')->default(0);
                $table->timestamp('cooldown_until')->nullable();
                $table->timestamps();
            });
        }

        // 2. Caroline 500 True Crime Cases catalog
        if (! Schema::hasTable('true_crime_cases')) {
            Schema::create('true_crime_cases', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('case_number')->unique(); // 1 to 500
                $table->string('title');
                $table->string('category')->default('Moord');
                $table->string('difficulty')->default('Gemiddeld');
                $table->text('incident_report');
                $table->json('clues');
                $table->json('suspects');
                $table->unsignedTinyInteger('culprit_index'); // 0, 1, 2, 3
                $table->text('solution_explanation');
                $table->unsignedInteger('reward_cash')->default(40000);
                $table->unsignedInteger('reward_xp')->default(50);
                $table->integer('reward_respect')->default(15);
                $table->timestamps();

                $table->index('case_number');
            });
        }

        // 3. Caroline True Crime Player Progress & Cooldown
        if (! Schema::hasTable('character_true_crime_states')) {
            Schema::create('character_true_crime_states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->unique()->constrained('characters')->cascadeOnDelete();
                $table->unsignedInteger('current_case_id')->nullable();
                $table->json('solved_case_ids')->nullable();
                $table->json('failed_case_ids')->nullable();
                $table->unsignedInteger('cases_solved_count')->default(0);
                $table->unsignedInteger('cases_failed_count')->default(0);
                $table->unsignedBigInteger('total_rewards_cash')->default(0);
                $table->unsignedBigInteger('total_rewards_respect')->default(0);
                $table->timestamp('cooldown_until')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_true_crime_states');
        Schema::dropIfExists('true_crime_cases');
        Schema::dropIfExists('character_connect_four_stats');
    }
};
