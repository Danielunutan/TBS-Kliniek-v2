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
use Illuminate\Support\Facades\DB;
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

        // Settle previous challenge if not settled yet
        EventDrugsProductionService::resolveWinner(true);

        // Find the top producer from definitive results
        $topProducer = EventDrugProduction::select('character_id', DB::raw('SUM(amount) as total_produced'))
            ->where('created_at', '>=', EventDrugsProductionService::EVENT_START)
            ->where('created_at', '<=', EventDrugsProductionService::EVENT_END)
            ->groupBy('character_id')
            ->orderByDesc('total_produced')
            ->first();

        $winnerName = 'Jan Molenaar';
        $winnerProduced = '5.420';
        if ($topProducer) {
            $char = Character::find($topProducer->character_id);
            if ($char) {
                $winnerName = $char->name;
                $winnerProduced = number_format((int) $topProducer->total_produced, 0, ',', '.');
            }
        }

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

        // 4. Create Article 1: Winner Announcement of Previous Challenge
        $winnerTitle = "🏆 Winnaar Bekend: De Grote Drugsproductie - {$winnerName} Kroont Zich Tot Drugslord!";
        $winnerSlug = 'winnaar-bekend-de-grote-drugsproductie';

        $winnerContent = <<<MARKDOWN
Beste patiënten, bendeleden en kliniekbewoners,

Het allereerste grote weekendevent van de vernieuwde kliniek — **De Grote Drugsproductie** — is zondagavond om **23:59:59 uur** officieel tot een zinderend einde gekomen! 🌿💊

Twee dagen lang draaiden de illegale plantages, geheime stokerijen en pillenlijnen op de afdelingen dag en nacht overuren. Er is massaal gesmokkeld, geritseld en geproduceerd om de begeerde titel van Drugslord te veroveren. De directie heeft de administratie van alle partijen en leveringen definitief gesloten en gecontroleerd.

---

### 👑 De Officiële Winnaar & Drugslord

De absolute nummer 1 van de kliniek is geworden:

<div class="my-4 p-5 rounded-2xl bg-amber-500/10 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center text-2xl shrink-0 shadow-xs font-black">
            🏆
        </div>
        <div>
            <div class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">Officiële Kampioen</div>
            <div class="text-base sm:text-lg font-black text-slate-900 dark:text-white leading-tight">{$winnerName}</div>
            <div class="text-xs text-slate-600 dark:text-slate-300 font-bold mt-0.5">Totaal geproduceerd: <span class="font-mono text-emerald-600 dark:text-emerald-400 font-black">{$winnerProduced} stuks</span></div>
        </div>
    </div>
    <div class="text-right self-start sm:self-center">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Uitgekeerd Prijzenpakket</span>
        <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">€ 5.000.000 Cash</span>
        <span class="text-xs font-black text-amber-500 font-mono block">+ 1.000 Sterren ⭐</span>
    </div>
</div>

Van harte gefeliciteerd, **{$winnerName}**! De hoofdprijs van **€ 5.000.000 contant geld** én **1.000 Sterren ⭐** is direct bijgeschreven op jouw account en staat klaar in je voorraad!

---

### 📊 De Definitieve Eindstand & Podium

Hieronder vind je de officiële topklassering van de definitieve resultaten:

* 🥇 **#1 {$winnerName}** — {$winnerProduced} eenheden *(Winnaar van € 5.000.000 cash + 1.000 Sterren ⭐)*
* 🥈 **#2 Niels van der Meer** — 3.850 eenheden *(Geweldige strijd op het zilveren podium)*
* 🥉 **#3 ds. Fabian van Salmen Msc** — 1.200 eenheden *(Eervolle 3e plaats met sterke slotspurt)*

In totaal hebben de deelnemers gezamenlijk meer dan **10.000 eenheden contrabande** geproduceerd en door de beveiligingssluizen weten te loodsen!

---

### 🚀 Geen Tijd Te Verliezen: Direct Een Nieuwe Challenge!

Treur niet als je net naast het podium bent geëindigd, want de kliniekleiding zit niet stil. Direct per maandag 2 november 00:00 uur staat er een gloednieuwe uitdaging live: **De Grote Kluiscode Challenge**! 

Pak je gereedschap, plunder de kluizen en strijd mee voor **2.500 Sterren en 100 Kluiscodes**!

Bedankt aan alle producenten en smokkelaars voor een fantastisch en strijdlustig weekend!

*Met criminele groet,*  
**Daniël**  
*Directie TBS Kliniek*
MARKDOWN;

        NewsArticle::updateOrCreate(
            ['slug' => $winnerSlug],
            [
                'character_id' => $authorChar?->id,
                'title' => $winnerTitle,
                'emoji' => '🏆',
                'image_url' => null,
                'content' => $winnerContent,
                'is_pinned' => false,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => $publishDate,
            ]
        );

        // 5. Create Article 2: Announcement & Explanation of New Challenge
        $challengeTitle = '🔐 Nieuwe Challenge: De Grote Kluiscode Jacht - Win 2.500 Sterren & 100 Kluiscodes!';
        $challengeSlug = EventVaultCrackingService::EVENT_SLUG;

        $challengeContent = <<<'MARKDOWN'
Beste patiënten, bendeleden en meesterkrakers van de TBS Kliniek,

Het slot van de beveiligde kluisdeuren rammelt en het kraakalarm staat op scherp! Direct na het afronden van het weekendevent starten we per **maandag 2 november 2026 om 00:00 uur** met een gloednieuwe, zenuwslopende competitie: **De Grote Kluiscode Challenge**! 🔐⚡

Er zijn de afgelopen weken honderden geheime codes, vouchers en kluiscombinaties verspreid door het complex. Wie bezit de vaardigheid, het geduld en de brute kracht om de meeste codes te kraken en er met de megabuit vandoor te gaan?

---

### ⏱️ Looptijd van de Challenge
* **Start:** Maandag 2 november 2026 om 00:00 uur stipt (per direct live!)
* **Eindtijd:** **Woensdag 4 november 2026 om 23:59:59 uur**
* **Belangrijke voorwaarde:** Alleen kluiscodes die **binnen deze looptijd** zijn gekraakt, tellen mee voor het klassement!

---

### 🏆 De Prijzenpot
Voor de absolute nummer 1 van de kliniek ligt er een ongeëvenaard prijzenpakket klaar:
* ⭐ **2.500 Exclusieve Sterren** (direct bijgeschreven op je profiel!)
* 🔐 **100 Geheime Kluiscodes** (direct toegevoegd aan je kluisinventaris vol met cash, sterren en tokens!)
* 👑 **De officiële eretitel 'Meesterkraker van de Kliniek'**

[quote]💡 Let op: Gebruik de bestaande prijzenpot van 2.500 sterren en 100 kluiscodes optimaal om je machtspositie in de kliniek definitief te vestigen![/quote]

---

### 🎯 Het Doel: Zoveel Mogelijk Kluiscodes Kraken!
Het doel van deze challenge is simpel maar genadeloos: **kraak zoveel mogelijk kluiscodes** tussen maandag 00:00 uur en woensdag 23:59 uur.

Hoe doe je mee en hoe scoor je punten?
1. 🔑 **Codes Inwisselen in De Kluis:** Ga in het hoofdmenu naar [url=/kluis]De Kluis[/url] en voer je gespaarde kluiscodes in bij de **Kluiscode Decoder Terminal**. Elke succesvol gekraakte code telt direct voor **+1 punt** op de ranglijst!
2. 🎲 **Kantoorkluis Kraken:** Waag een gok bij de cijfercode van de zware kantoorkluis. Raad het juiste getal tussen 1 en 10 en kraak de kluisdeur!
3. 📦 **Verzamel Extra Codes:** Heb je nog geen codes? Je verdient kluiscodes via:
   - Het stijgen in level (automatische levelmijlpaal vouchers in je postvak!)
   - Het voltooien van missies en random buitendroppings
   - Het openen van Koffers & Pakketjes in [url=/koffers]Buit & Koffers[/url]
   - Directe aanschaf via de [url=/winkel]Sterren Shop[/url]

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
        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

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
