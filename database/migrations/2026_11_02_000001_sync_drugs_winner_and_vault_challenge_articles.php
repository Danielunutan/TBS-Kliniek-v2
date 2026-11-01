<?php

use App\Models\Character;
use App\Models\NewsArticle;
use App\Services\EventDrugsProductionService;
use App\Services\EventVaultCrackingService;
use Carbon\Carbon;
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

        // 1. Resolve the previous challenge if needed and sync the winner article from actual leaderboard rows
        EventDrugsProductionService::resolveWinner(true);
        EventDrugsProductionService::syncWinnerArticle();

        // 2. Ensure the vault challenge article uses hyphen list items for clean Markdown rendering
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

        NewsArticle::where('is_pinned', true)
            ->where('slug', '!=', $challengeSlug)
            ->update(['is_pinned' => false]);

        NewsArticle::updateOrCreate(
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive content sync
    }
};
