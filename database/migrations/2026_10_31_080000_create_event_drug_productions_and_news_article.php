<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\NewsArticle;
use App\Services\EventDrugsProductionService;
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
        if (! Schema::hasTable('event_drug_productions')) {
            Schema::create('event_drug_productions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->unsignedBigInteger('amount');
                $table->string('drug_type', 50)->default('drugs');
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['created_at', 'character_id']);
            });
        }

        $slug = EventDrugsProductionService::EVENT_SLUG;
        $title = '🌿 Het Weekend Event: De Grote Drugsproductie - Win €5.000.000 & 1.000 Sterren!';

        $content = <<<'MARKDOWN'
Beste patiënten, bendeleden en smokkelaars van de TBS Kliniek,

Er staat per direct een gloednieuw weekendevent live: **De Grote Drugsproductie**! 🌿💊

De vraag naar illegale medicatie, gedraaide peuken en vers geoogste wiettoppen op de afdelingen is geëxplodeerd. De kliniekleiding kijkt dit weekend tijdelijk de andere kant op. Wie van jullie stampt de grootste illegale productielijn uit de grond en kroont zich tot de onbetwiste Drugslord van Paviljoen 13?

---

### 🏆 De Gigantische Hoofdprijs
Voor de absolute nummer 1 op het klassement ligt er een gigantisch prijzenpakket klaar:
* 💰 **€ 5.000.000 Contant Geld** (direct op je zak!)
* ⭐ **1.000 Gratis Sterren** (direct bijgeschreven op je profiel!)
* 👑 **Eeuwige roem en de titel 'Drugslord van de Kliniek'**

---

### ⏱️ Looptijd van het Event
* **Start:** Zaterdag 31 oktober 2026 (direct geopend!)
* **Eindtijd:** **Zondag 1 november 23:59:59** stipt!
* Om 23:59:59 wordt de eindstand direct en 100% automatisch verwerkt. De winnaar ontvangt de miljoenenbuit en de 1.000 sterren per direct op zijn account!

---

### 🏭 Hoe Doe Je Mee?
Iedereen kan direct meedoen, ongeacht je level of bende! Elk type productie telt mee:
1. 🌿 **Ondergrondse Productiekelder:** Ga via het menu naar [url=/smokkelen]Smokkelen & Productie[/url]. Draai pakjes sigaretten, ritsel strips Ritalin of kweek geurige Nederwiet toppen!
2. 💊 **Ziekenboeg Medicijnen:** Ritsel stiekem pillen weg onder je tong bij Zuster Brenda via [url=/ziekenboeg]De Ziekenboeg[/url]!
3. 🏢 **Bende Productiepanden:** Laat je wietkwekerij, chemisch lab of synthesecomplex op volle toeren draaien!

[quote]💡 Tip: Gebruik je gratis Sterren ⭐ om wachttijden en cooldowns direct te omzeilen in de Productiekelder voor maximale productie per uur![/quote]

---

### 📊 Live Tussenstand & Klassement
Hieronder zie je de actuele live stand. De ranglijst ververst automatisch elke 15 seconden. Strijd mee, hou je concurrenten nauwlettend in de gaten en pak die 5 miljoen en 1.000 sterren!

[drug_event_leaderboard]

Heel veel succes en moge de slimste producent winnen!

*Met criminele groet,*  
**Daniël**
MARKDOWN;

        // Try to attach author to Daniel or an admin/owner character if present
        $senderChar = null;
        if (Schema::hasTable('characters')) {
            $senderChar = Character::where('name', 'Daniël')
                ->orWhere('name', 'Daniel')
                ->orWhere('name', 'like', '%daniel%')
                ->orWhere('name', 'like', '%daniël%')
                ->orWhereHas('user', function ($q) {
                    $q->where('email', 'danielunutan@icloud.com')
                        ->orWhere('email', 'like', '%daniel%')
                        ->orWhere('name', 'like', '%daniel%')
                        ->orWhereIn('role', ['owner', 'admin']);
                })
                ->first() ?? Character::first();
        }

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $senderChar?->id,
                'title' => $title,
                'emoji' => '🌿',
                'image_url' => null,
                'content' => $content,
                'is_pinned' => true,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => now(),
            ]
        );

        if ($senderChar && Schema::hasTable('chat_messages')) {
            try {
                $newsUrl = route('news.show', $article->slug);
                $chatMessageText = "🚨 **[SYSTEEM MELDING - DIRECTIE]**: Het weekend event **De Grote Drugsproductie** is zojuist gestart! 🌿 De #1 op zondag 23:59 wint automatisch **€5.000.000 contant geld én 1.000 Sterren ⭐**! Bekijk het live klassement in het nieuwsbericht: {$newsUrl}";

                foreach (['global', 'algemeen'] as $ch) {
                    ChatMessage::firstOrCreate(
                        [
                            'character_id' => $senderChar->id,
                            'channel' => $ch,
                            'message' => $chatMessageText,
                        ],
                        [
                            'hearts_count' => 0,
                            'hearts_users' => [],
                        ]
                    );
                    Cache::forget("chat_last_id_{$ch}");
                }
            } catch (Throwable) {
                // Ignore chat broadcast errors if run in minimal test context
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NewsArticle::where('slug', EventDrugsProductionService::EVENT_SLUG)->delete();
        Schema::dropIfExists('event_drug_productions');
    }
};
