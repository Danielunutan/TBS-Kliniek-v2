<?php

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\EventDrugsProductionService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $slug = EventDrugsProductionService::EVENT_SLUG;
        $article = NewsArticle::where('slug', $slug)->first();

        if (! $article) {
            return;
        }

        // Locate Daniel's account in the database
        $danielUser = User::where('email', 'danielunutan@icloud.com')
            ->orWhere('email', 'like', '%daniel%')
            ->orWhere('name', 'like', '%daniel%')
            ->first();

        $danielChar = $danielUser?->character;

        if (! $danielChar) {
            $danielChar = Character::where('name', 'Daniël')
                ->orWhere('name', 'Daniel')
                ->orWhere('name', 'like', '%daniel%')
                ->orWhere('name', 'like', '%daniël%')
                ->first();
        }

        if (! $danielChar) {
            $danielChar = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin']);
            })->first();
        }

        if ($danielChar) {
            $article->character_id = $danielChar->id;
        }

        // Update the article sign-off to Daniel
        $updatedContent = str_replace(
            ['**De Kliniekdirectie & Hoofd Zaalwacht**', 'De Kliniekdirectie & Hoofd Zaalwacht'],
            '**Daniël**',
            $article->content
        );

        $article->content = $updatedContent;
        $article->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reversal needed
    }
};
