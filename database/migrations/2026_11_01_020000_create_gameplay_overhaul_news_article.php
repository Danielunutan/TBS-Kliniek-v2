<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const ARTICLE_SLUG = 'grote-gameplay-update-6-vernieuwde-kliniek-systemen';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('news_articles')) {
            return;
        }

        $slug = self::ARTICLE_SLUG;
        $title = '⚡ Grote Gameplay & Systeem-Update: 6 Onderdelen Volledig Vernieuwd!';

        $content = <<<'MARKDOWN'
Beste patiënten, bendeleiders en smokkelaars van de TBS Kliniek,

Vandaag is er een grote gameplay- en interface-update doorgevoerd op **6 belangrijke onderdelen** van de kliniek! Niet alleen zijn de schermen voorzien van een compleet nieuw, overzichtelijk dashboard-ontwerp met live voortgangsbalken, sliders en interactieve kaarten, maar elk onderdeel heeft ook **gloednieuwe tactische keuzes en beloningsmechanismen** gekregen.

Hieronder vind je een compleet en gedetailleerd overzicht van alles wat er is veranderd en hoe je er direct maximaal profijt uit haalt!

---

### 🔥 1. 24 Uur Battle — Klinische Etmaal-Escalatie
De [url=/24-uur-battle]24 Uur Battle[/url] is getransformeerd naar het **Klinische Etmaal-Escalatie** systeem met drie grote vernieuwingen:
- **Dagelijkse Focus-Overdrive (1.5x / 2.0x Multiplier):** Kies elke dag één van de **7 disciplines** (*Straatgezag & Eerbetoon*, *Kleine Vergrijpen & Ritselwerk*, *Financiële Transacties*, *Celblok-Bevrijdingen*, *Laboratorium-Synthese*, *Zwarte-Markt Afzet* of *Eliminaties & Afrekeningen*) als jouw primaire **Dagfocus**. Op die discipline ontvang je de hele dag **1.5x score-opbouw**!
- **Klinisch Spitsuur (12:00–14:00 & 20:00–22:00):** Tijdens de wisseling van de wacht schiet je gekozen Focus-discipline automatisch naar **2.0x Overdrive**!
- **Persoonlijke Escalatie-Punten (EP) & 3 Mijlpaal-Kisten:** Elke actie levert nu gewogen **Escalatie-Punten (EP)** op, zelfs als je niet in de top 5 staat. Bereik **100 EP** (*Kist I: € 15.000 + 2 Bivakmutsen 🎭*), **350 EP** (*Kist II: € 45.000 + 5 Bivakmutsen 🎭 + 5 Sterren ⭐*) en **900 EP** (*Kist III: € 100.000 + 10 Sterren ⭐ + 1 XXL Koffer 🧳*) en claim je beloningen direct op de pagina!
- **Nieuw Eretribune-Podium:** Elke categorie toont nu een visueel Top-3 eretribune-podium én een persoonlijke voortgangsbalk richting de top 5.

---

### 🌿 2. Dealen & Smokkel — Contrabande-Lab & Koeriersnetwerk
Het [url=/smokkelen]Smokkelen & Dealen[/url] scherm is vernieuwd naar een interactief laboratorium- en logistiekdashboard:
- **Kies je Tactische Transportroute:** Bij elke verkoop aan een afdelingskoper kies je nu zelf hoe je de waar aflevert:
- **Ondergrondse Sluiproute:** **100% veilig**, reguliere uitbetaling (**1.00x**) zonder onderscheppingsrisico.
- **Gepantserde Koerier:** **85% slaagkans**, levert **+15% zwarte-marktpremie (1.15x)** én **+2 Respect** op (bij onderschepping gaat 50% van de zending verloren).
- **Brute Stormloop:** **70% slaagkans**, levert **+25% toppremie (1.25x)**, **+5 Respect** én **+25% extra XP** op (bij onderschepping wordt de volledige lading in beslag genomen).
- **Interactieve Slider & Opslagsilo's:** Bepaal met een live schuifregelaar exact hoeveel stuks je verhandelt en zie direct je verwachte contante opbrengst per transportroute.

---

### 📦 3. Koffers & Pakketjes — Goederensluis & Buit-Kraakcentrum
Op de pagina [url=/pakketjes]Koffers & Pakketjes[/url] heb je voortaan volledige controle over hoe je buitgemaakte koffers openbreekt:
- **Kies uit 3 Visuele Kisten:** Selecteer voor het kraken **Kist Alpha**, **Kist Beta** of **Kist Omega** in het Kraak-Laboratorium.
- **3 Kraak-Methodes met Risico & Beloning:**
- **Precisie-Ontgrendeling:** **100% veilig**, garandeert **1.00x** standaard bankbuit en intacte inhoud.
- **Thermische Lans:** **75% kans** op een schone snede met **+35% extra bankgeld (1.35x)**; bij oververhitting (25%) blijft **0.40x** restwaarde over.
- **Hydraulisch Forceren:** **60% kans** op een voltreffer met **+75% extra bankgeld (1.75x)** én verhoogde kans op een **Kluiscode of Sterren ⭐**; bij een verfpatroon-explosie (40%) blijft slechts **0.15x** restwaarde over.
- **Nieuwe Indeling:** De 5 goederensluis-doelwitten staan nu overzichtelijk bovenaan, gevolgd door het Kraak-Laboratorium en een forensische tijdlijn van de laatste onderscheppingen.

---

### 🧤 4. Zakkenrollen — Vingervlugheid & Corridor-Observatie
[url=/zakkenrollen]Zakkenrollen[/url] is uitgebreid met een dynamische **Vleugel-Alertheid Meter** en drie gespecialiseerde greep-technieken:
- **Vleugel-Alertheid (0%–100%) & Wachtpatroon Observeren:** Elke zakkenrolpoging verhoogt de waakzaamheid op de gang (en een mislukking laat deze flink stijgen). Is de alertheid laag (**≤ 25%**), dan krijg je **+6% extra slaagkans**; bij hoge alertheid (**≥ 70%**) daalt je kans met **-8%**.
- **Nieuwe Actie — Observeren (2 Energie):** Verken de wachtposten om de **Vleugel-Alertheid direct met 25% te verlagen** én een **+5% Verkennings-Bonus** te activeren op je eerstvolgende greep!
- **3 Vingervlugheid-Technieken:**
- **Schaduwgreep:** **+8% extra slaagkans**, voorzichtige greep (**0.90x** contante buit).
- **Afleidingsmanoeuvre:** **Standaard slaagkans**, volledige buit (**1.00x**) en **+15% extra XP**.
- **Bliksemgreep:** **-6% slaagkans**, maar **+35% extra contante buit (1.35x)** én **+1 Respect** bij succes!

---

### 🛒 5. Winkel — Zwarte Markt & Kantine-Bazaar
De [url=/winkel]Kliniek Winkel[/url] is omgebouwd tot een moderne **Grid-Bazaar** met slimme hulpmiddelen:
- **Aanbieding van de Dag (-15% Extra Korting):** Elke dag selecteert de Zwarte Markt automatisch één willekeurig voorwerp als **Dagdeal** met **15% directe korting** (stapelbaar met je Charisma-korting tot maximaal 25% voordeel!).
- **Live Stat-Vergelijking met Huidig Dossier:** Op elke winkelkaart én in het nieuwe Dossier-Vergelijkingspaneel zie je met live voortgangsbalken precies hoe een wapen, pantser, gadget of consumptie jouw huidige **Kracht, Intelligentie, Uithoudingsvermogen, Charisma, Gezondheid en Energie** verhoogt ten opzichte van je level-maximum.
- **Vernieuwd Bundel-Grid:** Bulkbundels met 10% pakketkorting zijn nu direct vanuit een overzichtelijk kaarten-grid aan te schaffen.

---

### 🔒 6. Gevangenis — Isolatievleugel & Tactische Celkraak
Ook het [url=/gevangenis]Celblok & Isolatievleugel[/url] systeem heeft zowel voor ingesloten spelers als voor bevrijders nieuwe interacties gekregen:
- **Strafverkorting & Corridorsabotage (voor ingesloten spelers):** Zit je vast in Sector Z-04? Naast ontsnappen, vrijkopen of een sponsorvideo bekijken kun je nu elke 90 seconden actief je straftijd verkorten:
- **Stoomwasserij-Dienst (10 Energie):** **100% veilig** — verkort je resterende celstraf direct met **45 seconden** (of laat je per direct vrij als je tijd op 0 komt) én levert **+15 XP** op!
- **Nachtcipier Omkopen (€ 2.500):** **75% kans** dat de cipier je digitale celklok met **90 seconden terugdraait** (+1 Respect); **25% kans** dat de hoofdbewaker het ziet en er **30 seconden** bij op telt.
- **Tactische Uitbraak-Methode (voor vrije spelers):** Bij het uitbreken van een medepatiënt kies je voortaan per celdossier je aanpak:
- **Stille Sluipuitbraak (Lockpick):** **+6% hogere slaagkans**, **+2 Respect**, **+140 XP**, 25% kans op **+1 Intelligentie** en een kortere straf mocht je betrapt worden.
- **Breekijzer & Forceerwerk (Brute Kracht):** **-3% slaagkans**, maar levert bij succes **+4 Respect (dubbel!)**, **+210 XP**, **+0.2 Kraak-Index** én **40% kans op +1 Kracht** op!

[quote]💡 Tip: Duik direct de vernieuwde pagina's in, kies vandaag nog je **Dagfocus** in de [url=/24-uur-battle]24 Uur Battle[/url] en profiteer van de **Aanbieding van de Dag** in de [url=/winkel]Winkel[/url]![/quote]

Heel veel succes en speelplezier met alle nieuwe functies!

*Met criminele groet,*  
**Daniël**
MARKDOWN;

        $senderChar = null;
        if (Schema::hasTable('characters')) {
            $danielUser = Schema::hasTable('users')
                ? User::where('email', 'danielunutan@icloud.com')
                    ->orWhere('email', 'like', '%daniel%')
                    ->orWhere('name', 'like', '%daniel%')
                    ->first()
                : null;

            $senderChar = $danielUser?->character;

            if (! $senderChar) {
                $senderChar = Character::where('name', 'Daniël')
                    ->orWhere('name', 'Daniel')
                    ->orWhere('name', 'like', '%daniel%')
                    ->orWhere('name', 'like', '%daniël%')
                    ->orWhereHas('user', function ($q) {
                        $q->whereIn('role', ['owner', 'admin']);
                    })
                    ->first() ?? Character::first();
            }
        }

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $senderChar?->id,
                'title' => $title,
                'emoji' => '⚡',
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
                $chatMessageText = "⚡ **[GROTE GAMEPLAY-UPDATE LIVE]**: 6 kliniek-onderdelen (**24 Uur Battle, Smokkelen, Pakketjes, Zakkenrollen, Winkel & Gevangenis**) zijn zojuist compleet vernieuwd met nieuwe tactieken, mijlpaal-kisten, dagdeals en strafverkorting! Lees het volledige overzicht in het nieuwsbericht: {$newsUrl}";

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
                // Ignore chat broadcast errors when running in minimal CLI/test context
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
