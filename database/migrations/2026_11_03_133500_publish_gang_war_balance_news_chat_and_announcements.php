<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\NewsArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Publish the official pinned News Article
        if (Schema::hasTable('news_articles')) {
            $author = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            })->first() ?? Character::orderBy('id')->first();

            $slug = 'bende-oorlog-balans-update-eerlijke-strijd-transparante-verdediging-enforcer-cap';
            $title = '🚨 Bende Oorlog Balans Update: Eerlijke Strijd, Transparante Verdediging & Enforcer Cap!';

            $content = <<<'MARKDOWN'
Beste bendeleiders, officieren en strijders van de TBS Kliniek!

Naar aanleiding van waardevolle feedback uit de community hebben we het **Bende Oorlog Systeem** grondig geanalyseerd en per direct geherbalanceerd. Vanaf heden zijn bendegevechten 100% transparant en is de verhouding tussen aanval en verdediging weer helemaal eerlijk en gebalanceerd!

Hieronder vind je alle verbeteringen die zojuist live zijn gegaan:

---

### 🛡️ 1. Transparante Verdediging & Enforcers Zichtbaar
Voorheen werd op het openbare bende-profiel en in de rivalen-lijst alleen de basisbescherming getoond. Hulpkrachten (zoals Enforcer-bewakers in panden) waren verborgen, waardoor verdedigingen onverwacht veel hoger uitvielen dan zichtbaar was.

* **Wat is er veranderd?**
  * Zowel op het bende-profiel als in het **Bende Oorlog** tabblad zie je nu direct de **Totale Verdediging** inclusief het exacte percentage van alle Enforcers (bijv. `Totale Verdediging: 1.488.000 (+40% Enforcers)`).
  * Ook de bevestigingspopup voor een overval toont nu exact de werkelijke verdedigingsrol inclusief alle Enforcer-bonussen. Geen verborgen verrassingen meer!

---

### ⚖️ 2. Maximum Cap op Enforcer-Verdediging (Max +100%)
In bende-panden kunnen specialistische Enforcers worden geplaatst om je bende te beveiligen. Voorheen zat hier geen maximum op, waardoor bendes met veel panden hun verdediging konden opstapelen tot honderden procenten en letterlijk onkwetsbaar werden.

* **De nieuwe regel:**
  * De totale verdedigingsbonus door Enforcers is nu **gelimiteerd tot maximaal +100%** (een maximale verdubbeling van de basisverdediging).
  * In **Bende Panden** zie je nu direct jullie actieve Enforcer-percentage (`+X% / +100%`) en wordt de knop om extra Enforcers aan te nemen automatisch geblokkeerd zodra je op `+100%` zit, zodat je nooit per ongeluk geld verspilt.
  * Bendes die boven de 100% zaten hebben hun overtollige Enforcers eervol zien vertrekken en de volledige aankoopkosten zijn **100% teruggestort in de Bende Kas**!

---

### ⚡ 3. Winkel Balans: +5.000 Power per Aankoop (1:1 met Bescherming)
Voorheen leverde een aankoop van Bescherming in de Bende Winkel +5.000 punten op, terwijl Power slechts +2.500 opleverde voor dezelfde €15.000 uit de gangpot.

* **De nieuwe regel:**
  * Power aanschaffen in de Bende Winkel levert nu **+5.000 Power** op per aankoop (was 2.500), precies gelijk aan Bescherming (+5.000).
  * Hierdoor groeit de aanvalskracht van bendes evenredig mee met de verdedigingsmogelijkheden en loont het om samen met je bendeleden te sparen voor oorlog!

---

### 🩸 4. Eerlijker Verliespercentage & Proportionele Verdedigingsschade
Als een aanval werd afgeslagen, verloor de aanvallende bende tussen de 5% en 12% van hun totale Power. Daarnaast werd bij een overwinning de 10%–20% verdedigingsschade berekend over de totale verdediging (inclusief Enforcers) en direct van de basisbescherming afgetrokken.

* **De nieuwe regels:**
  * **Afgeslagen aanval:** De schade aan de aanvallende bende bij verlies is verlaagd naar **2% tot 5% Power** (was 5% tot 12%).
  * **Geslaagde overval:** De verdediger verliest bij een nederlaag eerlijk **10% tot 20% van de verdediging**, proportioneel berekend zodat zowel basisbescherming als totale verdediging met exact 10% tot 20% dalen.

---

### 🎯 5. Korte Herinnering: Hoe werkt het gevecht?
* **Aanvaller:** Vecht uitsluitend met zijn **Power (Aanvalskracht)**. Eigen verdediging blijft thuis.
* **Verdediger:** Vecht uitsluitend met zijn **Totale Verdediging (Basis + Enforcers)**. Vijandige power verdedigt niet mee.
* **Gevechtsrol:** Beide partijen krijgen een rol van **85% tot 115% (±15%)**. De hoogste score wint!

Heel veel succes op het slagveld! Spaar met je bende, bewaak je panden en heers over FPC De Balk! 👑
MARKDOWN;

            NewsArticle::updateOrCreate(
                ['slug' => $slug],
                [
                    'character_id' => $author?->id,
                    'title' => $title,
                    'emoji' => '⚔️',
                    'image_url' => 'assets/illustrations/banner_gangs.jpg',
                    'content' => $content,
                    'is_pinned' => true,
                    'comments_enabled' => true,
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );
        }

        // 2. Broadcast announcement to live chat
        if (Schema::hasTable('chat_messages') && Schema::hasTable('characters')) {
            $botChar = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            })->first() ?? Character::orderBy('id')->first();

            if ($botChar) {
                $broadcastText = '🚨 UPDATE: Bende Oorlogen zijn 100% geherbalanceerd! Enforcer-verdediging is nu overal zichtbaar en gecapt op max +100% (met 100% kas-terugbetaling voor overtollige Enforcers), Power in de winkel is verdubbeld (+5.000) en gevechtsschade is eerlijk rechtgetrokken. Check het nieuwsbericht! ⚔️';

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

        // 3. Send in-game message and notifications to all Gang Leaders and Officers
        if (Schema::hasTable('gangs') && Schema::hasTable('game_messages')) {
            $gangs = Gang::with('members')->get();

            foreach ($gangs as $gang) {
                if ($gang->leader_character_id) {
                    // Check if message was already sent to avoid duplicate
                    $alreadyNotified = GameMessage::where('recipient_id', $gang->leader_character_id)
                        ->where('subject', 'like', '%[Bende Oorlog Update]%')
                        ->where('created_at', '>=', now()->subHours(2))
                        ->exists();

                    if (! $alreadyNotified) {
                        GameMessage::create([
                            'recipient_id' => $gang->leader_character_id,
                            'sender_id' => null,
                            'sender_name' => 'FPC De Balk Directie',
                            'subject' => '⚔️ [Bende Oorlog Update] Eerlijk Gevechtssysteem, Transparantie & Balans',
                            'content' => "Beste leiding van bende {$gang->name},\n\n"
                                ."Vanaf heden is het Bende Oorlog systeem geüpdatet met belangrijke balansverbeteringen voor een eerlijke strijd:\n\n"
                                ."• 🛡️ **Totale Verdediging Zichtbaar:** Zowel op jullie bende-profiel als in het oorlogsoverzicht zie je nu direct de werkelijke verdediging inclusief de actieve Enforcer-bonus (+X% Enforcers).\n"
                                ."• ⚖️ **Enforcer Cap (Max +100%):** De bonus van Enforcers is gemaximeerd op +100% (verdubbeling van de basisverdediging). Hierdoor kan geen enkele bende meer oneindig verdediging stapelen.\n"
                                ."• ⚡ **Power Winkelverdubbeling:** Power aanschaffen in de Bende Winkel (€15.000) levert nu **+5.000 Power** op (was 2.500)!\n"
                                ."• 🩸 **Verlaagde Verliesstraf:** Bij een afgeslagen aanval verliest de aanvaller nu slechts 2% tot 5% power (was 5% tot 12%).\n\n"
                                ."Lees het volledige nieuwsbericht op de nieuwspagina voor alle details!\n\n"
                                ."Met vriendelijke groet,\n"
                                .'De Directie van FPC De Balk',
                            'type' => 'gang',
                        ]);
                    }
                }

                if (Schema::hasTable('game_notifications')) {
                    $officers = $gang->members()
                        ->where(function ($q) use ($gang) {
                            $q->whereIn('gang_role', ['leader', 'co_leader'])
                                ->orWhere('id', $gang->leader_character_id)
                                ->orWhere('id', $gang->personal_shopper)
                                ->orWhere('id', $gang->money_magnet);
                        })
                        ->get()
                        ->unique('id');

                    foreach ($officers as $officer) {
                        GameNotification::create([
                            'character_id' => $officer->id,
                            'type' => 'gang',
                            'icon' => 'fa-shield-halved',
                            'title' => '⚔️ Bende Oorlog Balans Update',
                            'message' => 'Bende gevechten zijn geherbalanceerd! Enforcer-verdediging is nu transparant zichtbaar, gecapt op max +100% en Power in de shop is verdubbeld.',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NewsArticle::where('slug', 'bende-oorlog-balans-update-eerlijke-strijd-transparante-verdediging-enforcer-cap')->delete();
    }
};
