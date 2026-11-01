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

        // Only synchronize the news article content and links without re-running event payouts
        EventDrugsProductionService::syncWinnerArticle();
        EventVaultCrackingService::syncChallengeArticle();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive content sync
    }
};
