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
        Schema::table('characters', function (Blueprint $table) {
            $table->timestamp('last_daily_spin_at')->nullable()->after('today_respect_date');
            $table->unsignedInteger('daily_spins_today')->default(0)->after('last_daily_spin_at');
            $table->timestamp('last_streak_claim_at')->nullable()->after('daily_spins_today');
            $table->unsignedInteger('streak_days')->default(0)->after('last_streak_claim_at');
        });

        Schema::create('bounties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('placed_by_character_id')->constrained('characters')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('reason', 255)->nullable();
            $table->boolean('is_claimed')->default(false);
            $table->foreignId('claimed_by_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounties');

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['last_daily_spin_at', 'daily_spins_today', 'last_streak_claim_at', 'streak_days']);
        });
    }
};
