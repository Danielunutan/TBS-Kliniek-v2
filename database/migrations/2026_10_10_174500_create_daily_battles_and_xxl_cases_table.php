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
        Schema::table('characters', function (Blueprint $table): void {
            $table->unsignedInteger('xxl_cases')->default(0)->after('unopened_bags');
        });

        Schema::create('daily_battles', function (Blueprint $table): void {
            $table->id();
            $table->date('battle_date')->unique();
            $table->string('title')->default('Strijd Om De Stack 🏆');
            $table->boolean('is_settled')->default(false);
            $table->timestamp('settled_at')->nullable();
            $table->json('winners_data')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_battle_scores', function (Blueprint $table): void {
            $table->id();
            $table->date('battle_date')->index();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score_slay')->default(0);
            $table->unsignedInteger('score_hustles')->default(0);
            $table->unsignedBigInteger('score_payments')->default(0);
            $table->unsignedInteger('score_breakouts')->default(0);
            $table->unsignedInteger('score_drugs_made')->default(0);
            $table->unsignedInteger('score_drugs_sold')->default(0);
            $table->unsignedInteger('score_kills')->default(0);
            $table->timestamps();

            $table->unique(['battle_date', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_battle_scores');
        Schema::dropIfExists('daily_battles');

        Schema::table('characters', function (Blueprint $table): void {
            $table->dropColumn('xxl_cases');
        });
    }
};
