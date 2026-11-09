<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\NewsArticle;
use App\Services\EventHustleChallengeService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('event_hustle_entries')) {
            Schema::create('event_hustle_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->unsignedTinyInteger('hustle_id')->index();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['created_at', 'character_id']);
            });
        }

        // Publish or sync challenge article
        $challengeArticle = EventHustleChallengeService::syncChallengeArticle();

        // Broadcast Announcement in Chat
        $authorChar = $challengeArticle?->author ?? Character::where('name', 'Daniël')->orWhere('name', 'Daniel')->first() ?? Character::first();
        if ($authorChar && $challengeArticle && Schema::hasTable('chat_messages')) {
            try {
                $newsUrl = route('news.show', $challengeArticle->slug);
                $chatMsg = "🚨 **[SYSTEEM MELDING - DIRECTIE]** De nieuwe challenge **De Grote Hossel Challenge** is zojuist gestart! 🎲 Zet de meeste succesvolle hossels neer op Bookie, Shag Kartel of Telecom tot donderdag 23.59 uur en win **750 Sterren ⭐ én €150.000.000 contant**! Bekijk de live ranglijst {$newsUrl}";

                foreach (['global', 'algemeen'] as $ch) {
                    ChatMessage::firstOrCreate(
                        [
                            'character_id' => $authorChar->id,
                            'channel' => $ch,
                            'message' => $chatMsg,
                        ],
                        [
                            'hearts_count' => 0,
                            'hearts_users' => [],
                        ]
                    );
                    Cache::forget("chat_last_id_{$ch}");
                }
            } catch (Throwable) {
                // Ignore chat broadcast errors in testing context
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NewsArticle::where('slug', EventHustleChallengeService::EVENT_SLUG)->delete();
        Schema::dropIfExists('event_hustle_entries');
    }
};
