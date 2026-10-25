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
            $table->index('last_active_at', 'characters_last_active_at_idx');
            $table->index(['level', 'xp'], 'characters_level_xp_idx');
            $table->index(['respect', 'level'], 'characters_respect_level_idx');
            $table->index(['thefts_succeeded', 'respect'], 'characters_thefts_respect_idx');
            $table->index('is_in_prison', 'characters_is_in_prison_idx');
        });

        if (Schema::hasTable('news_articles')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->index(['is_published', 'is_pinned', 'created_at'], 'news_articles_published_pinned_created_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table): void {
            $table->dropIndex('characters_last_active_at_idx');
            $table->dropIndex('characters_level_xp_idx');
            $table->dropIndex('characters_respect_level_idx');
            $table->dropIndex('characters_thefts_respect_idx');
            $table->dropIndex('characters_is_in_prison_idx');
        });

        if (Schema::hasTable('news_articles')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->dropIndex('news_articles_published_pinned_created_idx');
            });
        }
    }
};
