<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const ARTICLE_SLUG = 'pvp-bescherming-bende-balans-update-community-poll';

    /**
     * Clamp stacked isolation protection to 12 hours, clamp cash protection to 24 hours, and publish the community poll balance news article.
     */
    public function up(): void
    {
        if (Schema::hasTable('characters')) {
            $maxIsolation = now()->addHours(Character::MAX_ISOLATION_HOURS);
            $maxCashShield = now()->addHours(Character::MAX_CASH_PROTECTION_HOURS);

            if (Schema::hasColumn('characters', 'protection_until')) {
                DB::table('characters')
                    ->whereNotNull('protection_until')
                    ->where('protection_until', '>', $maxIsolation)
                    ->update(['protection_until' => $maxIsolation]);
            }

            if (Schema::hasColumn('characters', 'cash_protection_until')) {
                DB::table('characters')
                    ->whereNotNull('cash_protection_until')
                    ->where('cash_protection_until', '>', $maxCashShield)
                    ->update(['cash_protection_until' => $maxCashShield]);
            }
        }

        if (Schema::hasTable('news_articles')) {
            $author = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            })->first() ?? Character::orderBy('id')->first();

            $title = '⚖️ Grote Balans-Update: Isolatie, Cash-Schild, IC-Tijd & Bende Ripdeals (Discord Poll)';

            $content = <<<'MARKDOWN'
Beste gedetineerden, schutters en bendeleiders van TBS Kliniek!

Jullie hebben massaal gestemd in onze **Discord Community Poll**, en de uitslag was overduidelijk: **"🔥 Alle 4 doorvoeren!"** kwam als grote winnaar uit de bus. Op basis van jullie feedback in de poll en de gesprekken in de community voeren we vandaag alle vier de balanspunten direct door.

Het doel van TBS Kliniek is een gezonde, eerlijke balans waarin bendes echt met elkaar kunnen rivaliseren, rippen en strijden, zonder dat spelers zich wekenlang achter gestapelde schilden kunnen verschuilen óf andersom eindeloos door dezelfde persoon worden gepest.

Hieronder vind je alle vernieuwingen die per direct actief zijn:

---

### 🔒 1. Vrijwillige Isolatie = Echt op Slot (Max 12 uur)
Voorheen konden spelers hun isolatie en bescherming dagen- of zelfs wekenlang opstapelen en ondertussen gewoon vrijuit geld blijven verdienen in de kliniek. Dat is vanaf nu verleden tijd:

* **Maximale duur van 12 uur:** Vrijwillige isolatie (en bescherming uit de winkel) kan tot **maximaal 12 uur** vooruit worden gezet. Alle bestaande isolatietijden boven de 12 uur zijn automatisch teruggebracht naar dit eerlijke maximum.
* **Echt op slot in je cel:** Zolang je vrijwillig in de isolatiecel zit, kun je niet worden aangevallen of bestolen, maar kun je zelf ook **geen actieve kliniekacties** uitvoeren (zoals misdaden, klussen, trainingen, reizen of smokkelen).
* **Geld veiligstellen blijft mogelijk:** Je kunt vanuit vrijwillige isolatie nog wél altijd de **Kliniekbank** gebruiken om bijvoorbeeld voor het slapengaan je contante geld veilig te storten.
* **2 uur afkoeltijd:** Breek je je isolatie vroegtijdig open om weer actie te ondernemen? Dan geldt er een **afkoeltijd van 2 uur** voordat je opnieuw in isolatie kunt duiken.

---

### 🛡️ 2. VIP Cash-Schild: 50% Buitreductie & Max 24 uur
Situaties waarin spelers met bijna €100 miljoen op zak een maand lang volledig onschendbaar rondliepen, haalden de spanning uit het spel. Het VIP Cash-Schild is daarom eerlijk herbalanceerd:

* **50% Buitreductie i.p.v. 100% immuniteit:** Met een actief VIP Cash-Schild ben je niet langer volledig immuun voor berovingen of gevechten, maar wordt de gestolen contante buit bij aanvallen, zakkenrollen en liquidaties **gehalveerd (-50%)**.
* **Niet meer stapelbaar tot maanden:** Het Cash-Schild heeft een harde limiet van **maximaal 24 uur** en kan pas opnieuw worden geactiveerd zodra je huidige schild is verlopen. Alle bestaande schilden van weken of maanden zijn direct teruggezet naar maximaal 24 uur.
* **Aanvallen = Schild kwijt:** Zodra je zelf een andere speler aanvalt, berooft of beschiet, vervalt zowel je isolatie als je VIP Cash-Schild direct!

---

### 🏥 3. Liquidatie met Echte IC-Tijd (20 min) & Anti-Pest Limiet
Wie iemand succesvol liquideert, wil dat die actie ook echt impact heeft — maar we waken er tegelijk voor dat één speler niet de hele dag door dezelfde rivaal wordt vastgezet:

* **20 Minuten Echte IC-Coma:** Wordt een doelwit tijdens een liquidatie naar **0 HP** geschoten, dan belandt diegene nu **20 minuten daadwerkelijk op de Intensive Care (IC-Coma)** voordat er weer gespeeld kan worden.
* **30 Minuten Medisch Schild:** Na een IC-opname ontvangt het slachtoffer automatisch **30 minuten medische herstelbescherming** om rustig te kunnen herstellen en bankieren.
* **Anti-Pest Daglimiet:** Een aanvaller kan hetzelfde doelwit **maximaal 3 keer per dag** liquideren, en een speler kan in totaal **maximaal 5 keer per dag** dodelijk worden geliquideerd voordat er automatisch een beschermend IC-schild activeert.

---

### 🧪 4. Bende Ripdeals: Rivaliserende Labs Overvallen!
Bendes hebben er vanaf vandaag een gloednieuwe manier bij om met elkaar te rivaliseren! Via **Bende Panden** kun je nu een **Ripdeal (Lab Overval)** uitvoeren op de productielabs van rivaliserende bendes:

* **Steel 35% van de lab-batch:** Overval een draaiend of oogstklaar wiet-, coke- of synthetisch lab van een vijandige bende en maak **35% van de volledige batchwaarde** buit!
* **50/50 Verdeling + Respect:** De helft van de gestolen buit gaat direct naar jouw **eigen contante geld**, en de andere helft wordt gestort in jullie **Bendekas**, bovenop **+25 Respect** en **+200 XP**.
* **2 uur Raid-Schild per lab:** Een overvallen lab krijgt na een geslaagde ripdeal gedurende **2 uur extra bewaking**, zodat hetzelfde pand niet eindeloos achter elkaar kan worden leeggeroofd.

---

### 🔍 Samen blijven we zoeken naar de optimale balans!
Deze update is een grote stap richting eerlijkere PvP en levendige bende-rivaliteit, rechtstreeks gebaseerd op jullie stemmen in de **Discord Poll**. Omdat een goede spelbalans altijd in beweging is, **blijven we de komende tijd actief zoeken naar de optimale balans**. We houden alle gevechten, schilden en ripdeals nauwlettend in de gaten en blijven bijsturen waar nodig.

Laat in de reacties onder dit nieuwsartikel of op Discord weten hoe deze veranderingen bevallen. Veel succes in de kliniek en pas goed op je labs! ⚖️🔥🔫
MARKDOWN;

            NewsArticle::where('is_pinned', true)
                ->where('slug', '!=', self::ARTICLE_SLUG)
                ->update(['is_pinned' => false]);

            NewsArticle::updateOrCreate(
                ['slug' => self::ARTICLE_SLUG],
                [
                    'character_id' => $author?->id,
                    'title' => $title,
                    'emoji' => '⚖️',
                    'image_url' => 'assets/illustrations/banner_gangs.jpg',
                    'content' => $content,
                    'is_pinned' => true,
                    'comments_enabled' => true,
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );
        }

        if (Schema::hasTable('chat_messages') && Schema::hasTable('characters')) {
            $botChar = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            })->first() ?? Character::orderBy('id')->first();

            if ($botChar) {
                $broadcastText = '⚖️ BALANS-UPDATE LIVE (Discord Poll): Isolatie max 12u (echt op slot), VIP Cash-Schild -50% buit (max 24u), 20 min IC-Coma bij liquidatie + anti-pestlimiet, én Bende Lab Ripdeals staan nu live! Lees het nieuwsartikel voor alle details! 🔥';

                $chatMsg = ChatMessage::create([
                    'character_id' => $botChar->id,
                    'channel' => 'global',
                    'message' => $broadcastText,
                    'hearts_count' => 0,
                    'hearts_users' => [],
                ]);

                Cache::put('chat_last_id_global', (int) $chatMsg->id, now()->addHours(24));
            }
        }

        if (Schema::hasTable('game_notifications') && Schema::hasTable('characters')) {
            $activeCharacters = Character::take(250)->get();

            foreach ($activeCharacters as $char) {
                GameNotification::create([
                    'character_id' => $char->id,
                    'type' => 'news',
                    'icon' => 'fa-scale-balanced',
                    'title' => '⚖️ Grote PvP & Balans-Update (Discord Poll)',
                    'message' => 'Alle 4 punten uit de Discord Poll (Isolatie max 12u, Cash-Schild 50%, 20m IC-Coma & Bende Lab Ripdeals) staan nu live!',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('news_articles')) {
            NewsArticle::where('slug', self::ARTICLE_SLUG)->delete();
        }
    }
};
