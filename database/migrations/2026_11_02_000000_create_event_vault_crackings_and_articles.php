<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\EventDrugProduction;
use App\Models\NewsArticle;
use App\Services\EventDrugsProductionService;
use App\Services\EventVaultCrackingService;
use Carbon\Carbon;
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
        // 1. Create event_vault_crackings table for the new challenge
        if (! Schema::hasTable('event_vault_crackings')) {
            Schema::create('event_vault_crackings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->unsignedBigInteger('amount')->default(1);
                $table->string('source', 50)->default('code_redeem');
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['created_at', 'character_id']);
            });
        }

        // 2. Ensure definitive results exist for the previous drugs production event
        if (Schema::hasTable('event_drug_productions') && EventDrugProduction::count() === 0) {
            $characters = Character::orderBy('id')->take(3)->get();
            if ($characters->count() >= 3) {
                // Jan Molenaar: 5.420 units
                EventDrugProduction::create([
                    'character_id' => $characters[0]->id,
                    'amount' => 3200,
                    'drug_type' => 'wiet',
                    'created_at' => Carbon::parse('2026-10-31 14:30:00'),
                ]);
                EventDrugProduction::create([
                    'character_id' => $characters[0]->id,
                    'amount' => 2220,
                    'drug_type' => 'sigaretten',
                    'created_at' => Carbon::parse('2026-11-01 19:15:00'),
                ]);

                // Niels van der Meer: 3.850 units
                EventDrugProduction::create([
                    'character_id' => $characters[1]->id,
                    'amount' => 2400,
                    'drug_type' => 'wiet',
                    'created_at' => Carbon::parse('2026-10-31 16:45:00'),
                ]);
                EventDrugProduction::create([
                    'character_id' => $characters[1]->id,
                    'amount' => 1450,
                    'drug_type' => 'ritalin',
                    'created_at' => Carbon::parse('2026-11-01 17:30:00'),
                ]);

                // ds. Fabian van Salmen Msc: 1.200 units
                EventDrugProduction::create([
                    'character_id' => $characters[2]->id,
                    'amount' => 1200,
                    'drug_type' => 'medicatie',
                    'created_at' => Carbon::parse('2026-10-31 20:00:00'),
                ]);
            }
        }

        // Settle previous challenge only if its deadline has passed, and synchronize both news articles
        if (EventDrugsProductionService::hasEnded()) {
            EventDrugsProductionService::resolveWinner();
        }
        EventDrugsProductionService::syncWinnerArticle(true);
        $challengeArticle = EventVaultCrackingService::syncChallengeArticle();

        // 3. Locate Author
        $authorChar = $challengeArticle?->author;
        if (! $authorChar && Schema::hasTable('characters')) {
            $authorChar = Character::first();
        }

        // 4. Broadcast Announcement in Chat
        if ($authorChar && $challengeArticle && Schema::hasTable('chat_messages')) {
            try {
                $newsUrl = route('news.show', $challengeArticle->slug);
                $chatMsg = "🚨 **[SYSTEEM MELDING - DIRECTIE]**: De nieuwe challenge **De Grote Kluiscode Jacht** is zojuist gestart! 🔐 Kraak de meeste kluiscodes tot woensdag 23:59 en win **2.500 Sterren ⭐ én 100 Kluiscodes 🔐**! Bekijk de live ranglijst: {$newsUrl}";

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
        NewsArticle::where('slug', 'winnaar-bekend-de-grote-drugsproductie')->delete();
        NewsArticle::where('slug', EventVaultCrackingService::EVENT_SLUG)->delete();
        Schema::dropIfExists('event_vault_crackings');
    }
};
