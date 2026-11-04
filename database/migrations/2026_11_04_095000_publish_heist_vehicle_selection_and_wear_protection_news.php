<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const ARTICLE_SLUG = 'heist-garage-update-slimme-voertuigselectie-beste-conditie-chop-shop-slijtagebescherming';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Publish official News Article
        if (Schema::hasTable('news_articles')) {
            $author = Character::whereHas('user', function ($q) {
                $q->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            })->first() ?? Character::orderBy('id')->first();

            $title = '🏎️ Heist & Garage Update: Slimme Voertuigselectie, Beste Conditie & Chop-Shop Slijtagebescherming!';

            $content = <<<'MARKDOWN'
Beste criminelen, vluchtcoureurs en bendeleiders van TBS Kliniek!

Vandaag lanceren we een belangrijke Quality of Life update voor het **Heist Planningsbord**, de **Smokkel Garage** en de **Bende Co-Op Heists**. Deze update is rechtstreeks tot stand gekomen dankzij scherpe feedback en suggesties van **RW24** via ons ticketsysteem!

Hieronder vind je alle vernieuwingen die zojuist live zijn geactiveerd:

---

### 🏎️ 1. Slimme Voertuigselectie (Altijd de Beste Conditie)
Voorheen keek het Heist-systeem bij solo-operaties strikt naar je primair actieve vluchtwagen. Als deze wagen na eerdere overvallen beschadigd was geraakt (of als je meerdere exemplaren van hetzelfde type bezat, zoals helikopters of BMW's), moest je handmatig wisselen om niet met een zware slagingskanskorting op pad te gaan.

* **Wat is er veranderd?**
  * Zowel bij **Solo Heists** als bij **Bende Co-Op Heists** kiest het spel nu **volledig automatisch** het snelste en best onderhouden voertuig uit je garage dat aan de eisen voldoet.
  * Heb je een wagen met 35% conditie actief staan, maar bezit je in je garage een tweede exemplaar van 100%? Het systeem selecteert automatisch de wagen van 100% voor een maximale slagingskansbonus!
  * Op het solo-planningsbord zie je direct welk voertuig wordt ingezet en wordt de knop niet langer onnodig geblokkeerd als je actieve wagen gereviseerd moet worden terwijl er een fris reservevoertuig klaarstaat.

---

### 🔧 2. Chop-Shop Garage: -25% Heist Slijtage & 20% Revisiekorting!
Bendevoertuigen worden intensief gebruikt tijdens grootschalige Bende Heists, waardoor fleet-wagens snel slijtage oplopen door politie-achtervolgingen.

* **Nieuwe Bende Pand Perk:**
  * Bendes die in het bezit zijn van het pand **Illegale Omkat & Chop-Shop Garage** genieten vanaf heden van **25% slijtagebescherming** op alle voertuigen tijdens geslaagde en mislukte Bende Co-Op Heists!
  * Daarnaast ontvangen bendeleden en officieren een vaste **20% korting** op alle revisiekosten voor bendevoertuigen in de Bende Garage.
  * Zodra jullie bende een Chop-Shop Garage bezit, is deze bonus direct zichtbaar met een groen schildje bij alle Bende Heists op het planningsbord.

---

### 💬 Bedankt voor het meedenken!
Dank aan **RW24** voor het aandragen van deze suggestie. Blijf je ideeën en feedback vooral insturen via het ticketsysteem — samen maken we TBS Kliniek elke week beter!

Heel veel succes bij jullie volgende grote kraak! 🏎️💨💰
MARKDOWN;

            NewsArticle::updateOrCreate(
                ['slug' => self::ARTICLE_SLUG],
                [
                    'character_id' => $author?->id,
                    'title' => $title,
                    'emoji' => '🏎️',
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
                $broadcastText = '🏎️ UPDATE: Slimme voertuigselectie op het Heist Planningsbord (automatisch je snelste wagen met beste conditie) en -25% slijtagebescherming via de Chop-Shop Garage staan live! Shoutout naar RW24 voor het idee! 🔥';

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

        // 3. Send general update notification to all characters
        if (Schema::hasTable('game_notifications') && Schema::hasTable('characters')) {
            $activeCharacters = Character::take(250)->get();

            foreach ($activeCharacters as $char) {
                GameNotification::create([
                    'character_id' => $char->id,
                    'type' => 'news',
                    'icon' => 'fa-car-side',
                    'title' => '🏎️ Heist & Garage Update',
                    'message' => 'Slimme voertuigselectie (beste conditie wagen) & Chop-Shop slijtagebescherming (-25%) staan live!',
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
