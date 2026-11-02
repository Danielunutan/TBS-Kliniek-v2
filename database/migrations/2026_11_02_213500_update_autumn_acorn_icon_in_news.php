<?php

use App\Models\NewsArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update the Autumn Event news article to use the acorn icon (🌰) for Gouden Eikels.
     */
    public function up(): void
    {
        if (! Schema::hasTable('news_articles')) {
            return;
        }

        $article = NewsArticle::where('slug', 'halloween-quest-voltooid-verzamelobjecten-en-start-herfst-event')->first();
        if (! $article) {
            return;
        }

        $article->content = str_replace(
            [
                '- 🍂 **Nieuwe Herfst-Activiteiten & Oogstmijlpalen:**',
                '**Gouden Eikels (🍂)**',
            ],
            [
                '- 🌰 **Nieuwe Herfst-Activiteiten & Oogstmijlpalen:**',
                '**Gouden Eikels (🌰)**',
            ],
            (string) $article->content
        );
        $article->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive content update.
    }
};
