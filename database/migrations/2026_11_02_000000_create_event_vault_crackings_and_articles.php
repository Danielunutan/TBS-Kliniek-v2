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

        // Settle previous challenge if not settled yet and synchronize winner news article
        EventDrugsProductionService::resolveWinner(true);
        EventDrugsProductionService::syncWinnerArticle();

        // 3. Locate Author
        $authorChar = null;
        if (Schema::hasTable('characters')) {
            $authorChar = Character::where('name', 'Daniël')
                ->orWhere('name', 'Daniel')
                ->orWhere('name', 'like', '%daniel%')
                ->orWhere('name', 'like', '%daniël%')
                ->orWhere('name', 'like', '%directie%')
                ->orWhereHas('user', function ($q) {
                    $q->where('email', 'danielunutan@icloud.com')
                        ->orWhere('email', 'like', '%daniel%')
                        ->orWhereIn('role', ['owner', 'admin']);
                })
                ->first() ?? Character::first();
        }

        $publishDate = Carbon::parse('2026-11-02 00:00:00', 'Europe/Amsterdam');

        // 4. Create Article 2: Announcement & Explanation of New Challenge
        $challengeTitle = '🔐 Nieuwe Challenge: De Grote Kluiscode Jacht - Win 2.500 Sterren & 100 Kluiscodes!';
        $challengeSlug = EventVaultCrackingService::EVENT_SLUG;

        $challengeContent = <<<'MARKDOWN'
Beste patiënten, bendeleden en meesterkrakers van de TBS Kliniek,

Het slot van de beveiligde kluisdeuren rammelt en het kraakalarm staat op scherp! Direct na het afronden van het weekendevent starten we per **maandag 2 november 2026 om 00:00 uur** met een gloednieuwe, zenuwslopende competitie: **De Grote Kluiscode Challenge**! 🔐⚡

Er zijn de afgelopen weken honderden geheime codes, vouchers en kluiscombinaties verspreid door het complex. Wie bezit de vaardigheid, het geduld en de brute kracht om de meeste codes te kraken en er met de megabuit vandoor te gaan?

---

### ⏱️ Looptijd van de Challenge
- **Start:** Maandag 2 november 2026 om 00:00 uur stipt (per direct live!)
- **Eindtijd:** **Woensdag 4 november 2026 om 23:59:59 uur**
- **Belangrijke voorwaarde:** Alleen kluiscodes die **binnen deze looptijd** zijn gekraakt, tellen mee voor het klassement!

---

### 🏆 De Prijzenpot
Voor de absolute nummer 1 van de kliniek ligt er een ongeëvenaard prijzenpakket klaar:
- ⭐ **2.500 Exclusieve Sterren** (direct bijgeschreven op je profiel!)
- 🔐 **100 Geheime Kluiscodes** (direct toegevoegd aan je kluisinventaris vol met cash, sterren en tokens!)
- 👑 **De officiële eretitel 'Meesterkraker van de Kliniek'**

[quote]💡 Let op: Gebruik de bestaande prijzenpot van 2.500 sterren en 100 kluiscodes optimaal om je machtspositie in de kliniek definitief te vestigen![/quote]

---

### 🎯 Het Doel: Zoveel Mogelijk Kluiscodes Kraken!
Het doel van deze challenge is simpel maar genadeloos: **kraak zoveel mogelijk kluiscodes** tussen maandag 00:00 uur en woensdag 23:59 uur.

Hoe doe je mee en hoe scoor je punten?
- 🔑 **1. Codes Inwisselen in De Kluis:** Ga in het hoofdmenu naar [url=/kluis]De Kluis[/url] en voer je gespaarde kluiscodes in bij de **Kluiscode Decoder Terminal**. Elke succesvol gekraakte code telt direct voor **+1 punt** op de ranglijst!
- 🎲 **2. Kantoorkluis Kraken:** Waag een gok bij de cijfercode van de zware kantoorkluis. Raad het juiste getal tussen 1 en 10 en kraak de kluisdeur!
- 📦 **3. Verzamel Extra Codes:** Je verdient extra kluiscodes via het stijgen in level (levelmijlpaal vouchers in je postvak), het voltooien van missies, het openen van koffers in [url=/koffers]Buit & Koffers[/url], of directe aanschaf via de [url=/winkel]Sterren Shop[/url]!

---

### 📊 Live Ranglijst & Klassement
Hieronder zie je het realtime rankingboard van de challenge. De ranglijst wordt tijdens de hele challengeperiode **automatisch elke 15 seconden bijgewerkt**. Per speler zie je de actuele positie, spelersnaam en het totale aantal gekraakte kluiscodes, gerangschikt van hoog naar laag.

[vault_challenge_leaderboard]

---

Pak je koevoet, verzamel al je vouchers en kraak jezelf naar de top van de TBS Kliniek!

Veel succes aan alle deelnemers!

*Met criminele groet,*  
**Daniël**  
*Directie TBS Kliniek*
MARKDOWN;

        // Unpin previous articles so the new challenge is the main pinned article
        NewsArticle::where('is_pinned', true)
            ->where('slug', '!=', $challengeSlug)
            ->update(['is_pinned' => false]);

        $challengeArticle = NewsArticle::updateOrCreate(
            ['slug' => $challengeSlug],
            [
                'character_id' => $authorChar?->id,
                'title' => $challengeTitle,
                'emoji' => '🔐',
                'image_url' => null,
                'content' => $challengeContent,
                'is_pinned' => true,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => $publishDate,
            ]
        );

        // 6. Broadcast Announcement in Chat
        if ($authorChar && Schema::hasTable('chat_messages')) {
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
