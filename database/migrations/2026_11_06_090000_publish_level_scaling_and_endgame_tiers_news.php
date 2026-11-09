<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const ARTICLE_SLUG = 'grote-balans-update-level-scaling-en-nieuwe-endgame-tiers';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // 1. Publish official News Article
        if (Schema::hasTable('news_articles')) {
            $author = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            })->first() ?? Character::orderBy('id')->first();

            $title = '📈 Grote Balans Update: Level-Scaling & Nieuwe Endgame Tiers!';

            $content = <<<'MARKDOWN'
Beste patiënten, bendeleden en meesterbreinen van TBS Kliniek!

Vandaag lanceren we een van de grootste balans- en progressie-updates van het seizoen: **Dynamische Level-Scaling** en gloednieuwe **High-Level Endgame Tiers**!

Deze update is rechtstreeks ontstaan dankzij de uitstekende feedback en suggestie van **Dennis** (Level 28) via ons support ticketsysteem. 

Voorheen gaven de klassieke activiteiten — zoals werken in de werkplaats, afpersen, kluisjes kraken en droppings — vaste contante bedragen en vaste XP. Daardoor werden deze klusjes op hogere levels financieel en qua voortgang minder aantrekkelijk vergeleken met liquidaties en grote heists. Vanaf nu loont elke activiteit mee met jouw level!

---

### 📈 1. Dynamische Level-Scaling (+4% Beloning per Level)
Vanaf heden worden zowel de **contante opbrengsten** als de **ervaringspunten (XP)** van 4 kernactiviteiten dynamisch verhoogd op basis van jouw spelerslevel:

* **Dagbesteding & Werkplaats** (`/dagbesteding`)
* **Afpersen & Chantage** (`/afpersen`)
* **Kluisjes Kraken** (`/kluisjes`)
* **Smokkel-Droppings** (`/smokkel-droppings`)

**Hoe werkt de formule?**
* **Level 1:** 100% basisbeloning (vertrouwde instap voor nieuwelingen).
* **Level 10:** **+36%** extra cash en XP.
* **Level 20:** **+76%** extra cash en XP.
* **Level 28:** **+108%** (meer dan het dubbele van de basis!).
* **Level 50+:** Tot maar liefst **+250% bonus** op contanten en ervaringspunten.

Op de actiepagina's zie je bovenaan direct een paarse badge met jouw actieve **Level Bonus**!

---

### 🔓 2. Nieuwe Ontgrendelbare High-Tier Opdrachten & Activiteiten

Naast de level multiplier hebben we voor alle 4 de categorieën exclusieve nieuwe opties toegevoegd voor gevorderde spelers:

#### 🔨 Dagbesteding & Werkplaats (`/dagbesteding`):
* **Level 10+ | Directie-administratie & Archiefbeheer:**  
  * Sorteer vertrouwelijke cliëntendossiers en facturen in het directiekantoor.  
  * *Vergoeding:* €4.500 – €9.000 + 160 XP (voor level-bonus!) + 25% kans op een vergeten directiefooi (€1.500–€3.500).
* **Level 20+ | Technische Dienst & Storingsbeheer:**  
  * Repareer noodaggregaten, elektronische deursluizen en beveiligingscamera's voor Werkmeester Henk.  
  * *Vergoeding:* €8.500 – €17.500 + 250 XP + 30% kans op **2 Kluiscodes**!

#### 👊 Afpersen & Chantage (`/afpersen`):
* **Level 15+ | Externe Witwas-Handelaar:**  
  * Dwing de corrupte leverancier van kliniekgoederen een deel van zijn zwarte omzet af.  
  * *Buit:* €8.000 – €22.000 contant + 500 XP en +4 respect.
* **Level 25+ | Inspecteur Gezondheidszorg:**  
  * Het ultieme chantage-doelwit. Chanteer de visiterende rijksinspecteur met compromitterend bewijsmateriaal.  
  * *Buit:* €15.000 – €45.000 contant + 800 XP en +4 respect.

#### 🗄️ Kluisjes Kraken (`/kluisjes`):
* **Level 15+ | Gepantserde Directietresor (Hoofdgebouw):**  
  * Zwaarbeveiligde stalen kluis met biometrisch slot en reserves voor directiebonussen.  
  * *Buit:* €8.500 – €24.000 contant + 600 XP en +3 respect.
* **Level 25+ | Ondergrondse Ministeriekluis:**  
  * Ongekraakte stalen kluis in de fundering vol diplomatiek zwart geld en staatsgeheimen.  
  * *Buit:* €16.000 – €50.000 contant + 950 XP en +3 respect.

#### 🚁 Smokkel-Droppings (`/smokkel-droppings`):
* **Level 15+ | Kartel Stealth-Helikopter Dropping:**  
  * Schakel een zwaar militair kartel in. Een geruisloze zwarte helikopter dropt een militaire waterdichte plunjezak op het dak.  
  * *Kosten:* €2.500 contant + 35 Energie.  
  * *Elite Buit:* **€6.000 – €16.000 contant** + 200–380 XP, zware hoeveelheden sigaretten, Ritalin, Oxazepam en kans op 2 Kluiscodes!

---

### 💬 Shoutout naar Dennis!
Dank aan **Dennis** voor het insturen van dit scherpe idee via ticket `TBS-T71-842`. Jouw suggestie zorgt ervoor dat de hele economie en progressie van TBS Kliniek naar een hoger niveau is getild!

Blijf suggesties en feedback vooral delen via het ticketsysteem — samen bouwen we aan de beste kliniekervaring!

Veel succes met de nieuwe klussen en kluizen! 📈💰🔨
MARKDOWN;

            NewsArticle::where('is_pinned', true)
                ->where('slug', '!=', self::ARTICLE_SLUG)
                ->where('slug', '!=', 'de-grote-kluiscode-challenge-2500-sterren-en-100-codes')
                ->update(['is_pinned' => false]);

            NewsArticle::updateOrCreate(
                ['slug' => self::ARTICLE_SLUG],
                [
                    'character_id' => $author?->id,
                    'title' => $title,
                    'emoji' => '📈',
                    'image_url' => 'assets/illustrations/banner_vault.jpg',
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
                $broadcastText = '📈 UPDATE: Level-scaling (+4% loon/buit & XP per level op werken, afpersen, kluisjes en droppings) én nieuwe Level 10/15/20/25 tiers staan nu live! Shoutout naar Dennis voor de topsuggestie! 🔥';

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

        // 3. Send general update notification to active characters
        if (Schema::hasTable('game_notifications') && Schema::hasTable('characters')) {
            $activeCharacters = Character::take(250)->get();

            foreach ($activeCharacters as $char) {
                GameNotification::create([
                    'character_id' => $char->id,
                    'type' => 'news',
                    'icon' => 'fa-arrow-trend-up',
                    'title' => '📈 Balans Update: Level-Scaling Live!',
                    'message' => 'Werken, afpersen, kluisjes en droppings groeien nu mee met je level (+4% per level) met nieuwe Level 10/15/20/25 tiers!',
                ]);
            }
        }

        // 4. Auto-solve support ticket TBS-T71-842 if present
        if (Schema::hasTable('support_tickets') && Schema::hasTable('support_ticket_messages')) {
            $ticket = SupportTicket::where('ticket_number', 'TBS-T71-842')
                ->orWhere('ticket_number', 'like', '%T71-842%')
                ->orWhere(function ($q) {
                    $q->where('subject', 'like', '%meegroeien met level%')
                        ->orWhere('subject', 'like', '%opbrengsten van diverse dingen%');
                })
                ->first();

            if ($ticket) {
                $alreadyReplied = $ticket->messages()
                    ->where('is_staff', true)
                    ->where('message', 'like', '%Level-Scaling%')
                    ->exists();

                if (! $alreadyReplied) {
                    $staffUser = User::where('role', 'owner')
                        ->orWhere('role', 'admin')
                        ->orWhere('is_staff', true)
                        ->first() ?? User::first();

                    $staffCharacter = $staffUser?->character
                        ?? Character::whereHas('user', fn ($q) => $q->where('is_staff', true))->first()
                        ?? Character::first();

                    $replyMessage = <<<'REPLY'
Beste Dennis,

Nogmaals ontzettend bedankt voor je scherpe feedback en suggestie! 📈⚡

Je observatie klopte als een bus: doordat de beloningen van Dagbesteding, Afpersen, Kluisjes kraken en Smokkel-droppings vaststonden, verloren ze op hogere levels hun aantrekkingskracht.

We hebben jouw feedback direct omgezet in actie en zojuist de grote **Level-Scaling & Endgame Tiers Update** live gezet:

1. **Dynamische Level-Scaling (+4% per level)**:
   - Op al deze 4 activiteiten groeien contante opbrengsten én XP nu automatisch mee met jouw spelerslevel.
   - Op jouw huidige level (Level 28) ontvang je direct **+108% bonus** op al het werk, de kluisjes, de afpersingen en de tennisbal/drone droppings!

2. **Nieuwe Unlockable High-Level Tiers**:
   - **Dagbesteding:** Nieuwe werkdiensten ontgrendeld op Level 10 (Directie-administratie) en Level 20 (Technische dienst & Storingsbeheer).
   - **Afpersen:** Nieuwe doelwitten ontgrendeld op Level 15 (Externe Witwas-Handelaar) en Level 25 (Inspecteur Gezondheidszorg).
   - **Kluisjes Kraken:** Nieuwe kluizen op Level 15 (Gepantserde Directietresor) en Level 25 (Ondergrondse Ministeriekluis).
   - **Smokkel-Droppings:** Vanaf Level 15 kun je nu een **Kartel Stealth-Helikopter Dropping** bestellen voor grootschalige buit!

Er staat tevens een officieel nieuwsbericht online met een eervolle vermelding voor jouw suggestie. Heel veel succes in de kliniek!

Met vriendelijke groet,
TBS Kliniek Staff
REPLY;

                    SupportTicketMessage::create([
                        'support_ticket_id' => $ticket->id,
                        'user_id' => $staffUser?->id,
                        'character_id' => $staffCharacter?->id,
                        'is_staff' => true,
                        'message' => $replyMessage,
                    ]);

                    $ticket->update([
                        'status' => 'beantwoord',
                        'last_reply_at' => now(),
                        'last_reply_by_staff' => true,
                    ]);

                    try {
                        AuditLogService::log(
                            action: 'support_ticket_reply',
                            character: $staffCharacter,
                            target: $ticket->character,
                            details: [
                                'ticket_id' => $ticket->id,
                                'ticket_number' => $ticket->ticket_number,
                                'note' => 'Auto-replied via level scaling balance update migration',
                            ]
                        );
                    } catch (Throwable $e) {
                        // Suppress log failures during migration
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
        if (Schema::hasTable('news_articles')) {
            NewsArticle::where('slug', self::ARTICLE_SLUG)->delete();
        }
    }
};
