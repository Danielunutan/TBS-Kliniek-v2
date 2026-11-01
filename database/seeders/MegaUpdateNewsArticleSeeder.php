<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\NewsArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class MegaUpdateNewsArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slug = 'mega-update-doelen-battles-bosses-liquidaties-koffers';

        $content = <<<'MARKDOWN'
Beste patiënten en bendeleden van de TBS Kliniek,

Er staat zojuist een gigantische spelupdate live! Velen van jullie vroegen zich af: *"Wat is nu precies het doel van het spel en waar strijd ik elke dag voor?"*. Daarnaast wilden spelers elkaars bloed onder de nagels vandaan kunnen halen zoals in klassieke misdaadgames. 

In deze update leggen we alles stap-voor-stap en **100% noob-friendly** uit, zodat iedereen direct maximaal kan profiteren!

---

### 1. 🎯 Wat is het Doel van TBS Kliniek?
In TBS Kliniek ben je niet zomaar opgesloten; je vecht voor **macht, rijkdom en respect** in de zwaarst bewaakte kliniek van Nederland!
* 👑 **De Respectlijst & Rangorde:** Versla rivalen, voer misdrijven uit en klim op tot de onbetwiste Koning van de Luchtplaats.
* ⚔️ **De Dagelijkse 24u Kliniek Battle:** Elke dag om 00:00 uur keert de kliniek enorme prijzen, jackpots en gratis Sterren ⭐ uit aan de top 3 van 5 categorieën (Misdrijven, Gevechten, Respect, Casino en Werkplaats).
* 👹 **Clan Bazen Neerhalen:** Richt een bende op of sluit je aan bij een bestaande gang! Daag gezamenlijk oppermachtige Zaalwacht Bazen uit met miljoenen HP. Ieder lid vecht mee en bij winst wordt de monsterlijke buit eerlijk verdeeld.

---

### 2. 🔫 Moord, Liquidaties, Kogels & Coma (Hitman PvP)
Wil je een bloedhond zijn en je vijanden echt dwarszitten? Dat kan nu via **Illegale Acties > Huurmoordenaar / Liquidatie**:
* 💣 **Schiet vijanden neer:** Koop scherpe kogels bij de Wapenhandelaar of verdien ze gratis via de Sponsorkamer. Kies je doelwit, bepaal je kogels en schiet!
* 🚑 **Intensive Care Coma:** Een geslaagde aanslag schiet het slachtoffer direct in een **6-uurs Intensive Care Coma**! Slachtoffers kunnen tijdelijk niet aanvallen of overvallen plegen.
* 🛡️ **Kogelwerend Vest & Ricochet Backfire:** Draag een Kogelwerend Vest! Dit verhoogt je verdediging met +150% én heeft een **45% ricochet-kans**. Schiet iemand op jou terwijl je een vest draagt? Dan kan de kogel afketsen en de aanvaller zélf in een coma schieten!

---

### 3. 📦 Koffers Onderscheppen & XXL Koffers Kraken
Onder **Voortgang & Opslag > Koffers** kun je verdachte smokkelkoffers onderscheppen die het kliniekterrein op worden gesmokkeld:
* 🔓 **Kraken & Forceren:** Gebruik lockpicks of breekijzers om koffers te openen.
* 💎 **XXL Koffers:** Dit zijn de meest zeldzame buitkratten in het spel. Ze bevatten gegarandeerd topbeloningen: tot wel €250.000 contant geld, gratis Sterren ⭐, scherpe munitie, maskers en exclusieve profielstickers!

---

### 4. 📺 Sponsor Ads: Verdien Alles Gratis Zonder Geld!
In TBS Kliniek hoef je **nooit echt geld** uit te geven. Via de vernieuwde Sponsorkamer kun je korte 15-seconden videoadvertenties bekijken voor krachtige spelvoordelen:
* 🔫 **+50 Scherpe Kogels:** Bekijk 3 korte sponsor ads en je munitie wordt direct aangevuld met 50 kogels!
* 🛡️ **12 Uur Kogelwerend Vest:** Bekijk 3 sponsor ads voor 12 uur vestbescherming tegen vijandige schutters.
* 💉 **Coma Ontwaken:** Neergeschoten door een vijand? Bekijk 3 sponsor ads via de rode balk bovenin en je ontwaakt per direct met 50% HP!
* ⚖️ **Isoleercel Strafvermindering & Direct Vrij:** Elke videoad verkort je straftijd met 3 minuten. Afhankelijk van de zwaarte van je straf word je bij 1 tot 3-5 ads direct volledig vrijgelaten!
* 👑 **24 Uur Gratis VIP:** Bij elke 250 bekeken advertenties ontgrendel je automatisch 24 uur VIP lidmaatschap!

---

### 5. 💡 Vragen? De Nieuwe Helpbot & FAQ Staan Voor Je Klaar!
Heb je een vraag over hoe een functie precies werkt? 
* Bezoek het vernieuwde **FAQ Kenniscentrum** via het menu.
* Of klik rechtsonder op de **AI Helpbot**! Typ bijvoorbeeld *"hoe werkt coma"*, *"bazen"*, *"kogels"* of *"vrijlating"* en je krijgt direct het antwoord met handige snelkoppelingen.

Heel veel succes in de kliniek, zet je schrap en strijd mee om de top!

*Met criminele groet,*  
**De Directie & Hoofdbeveiliging van TBS Kliniek**
MARKDOWN;

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => '🔥 Mega Update: Doelen, 24u Battles, Clan Bazen, XXL Koffers & Moord/Liquidaties!',
                'emoji' => '🔥',
                'image_url' => null,
                'content' => $content,
                'is_pinned' => true,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => now(),
            ]
        );

        // Find system/admin character or first character to post Plein Chat message
        $senderChar = Character::whereHas('user', function ($q) {
            $q->where('role', 'admin');
        })->first() ?? Character::first();

        if ($senderChar) {
            $newsUrl = route('news.show', $article->slug);
            $chatMessageText = "🚨 **[SYSTEEM MELDING - DIRECTIE]**: Er staat een enorme nieuwe update live! 🎯 **Doel van het spel, 24u Battles & Clan Bazen**, 🔫 **Moord & Liquidaties (Hitman)**, 📦 **XXL Koffers Kraken**, en 📺 **Ad beloningen (Kogels, Coma & Gevangenis verkorten)**. Lees het complete beginnersvriendelijke overzicht nu in het Nieuwscentrum: {$newsUrl}";

            foreach (['global', 'algemeen'] as $ch) {
                ChatMessage::firstOrCreate([
                    'character_id' => $senderChar->id,
                    'channel' => $ch,
                    'message' => $chatMessageText,
                ], [
                    'hearts_count' => 0,
                    'hearts_users' => [],
                ]);
                Cache::forget("chat_last_id_{$ch}");
            }
        }
    }
}
