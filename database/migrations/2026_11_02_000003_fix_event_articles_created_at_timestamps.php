<?php

use App\Services\EventDrugsProductionService;
use App\Services\EventVaultCrackingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('news_articles')) {
            return;
        }

        // Sync article timestamps (created_at & published_at = 2026-11-02 00:00:00) without touching payouts
        EventDrugsProductionService::syncWinnerArticle();
        EventVaultCrackingService::syncChallengeArticle();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive timestamp sync
    }
};
