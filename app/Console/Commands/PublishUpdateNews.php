<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('tbs:publish-update-news')]
#[Description('Publiceert de grote officiële update nieuwsbrief met uitleg over de frisse start, spelmechanieken en het doel van het spel')]
class PublishUpdateNews extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Zoek Daniel zijn account
        $danielCharacter = Character::where('id', 6)
            ->orWhere('name', 'like', '%𝕯𝖆𝖓𝖎𝖊𝖑%')
            ->orWhere('name', 'like', '%Daniel%')
            ->orWhere('username', 'like', '%daniel%')
            ->first();

        if (! $danielCharacter) {
            $danielUser = User::where('name', 'like', '%Daniel%')
                ->orWhere('email', 'like', '%daniel%')
                ->first();

            if ($danielUser && $danielUser->character) {
                $danielCharacter = $danielUser->character;
            }
        }

        if (! $danielCharacter) {
            $danielCharacter = Character::first();
        }

        $authorId = $danielCharacter?->id;
        $this->info("Auteur geselecteerd: ID {$authorId} ({$danielCharacter?->name})");

        // 2. Vorige update artikelen opruimen
        $deleted = NewsArticle::whereIn('slug', [
            'update-roterende-garage-en-grote-bende-heists',
            'grote-update-bende-uurloon-vastgoed-imperium-garage-vloot-bende-heists',
            'grote-update-alles-wat-nieuw-is-in-tbs-kliniek',
            'frisse-start-seizoen-2-sprint-30-dagen',
        ])
            ->orWhere('title', 'like', '%Roterende Garage en Grote Bende Heists%')
            ->orWhere('title', 'like', '%Grote Update: Nieuw Bende-Uurloon%')
            ->orWhere('title', 'like', '%Frisse Start: Seizoen 2 Sprint%')
            ->delete();

        $this->info("Vorige update artikelen opgeruimd ({$deleted} verwijderd)");

        // Pinned status beheren zodat dit artikel bovenaan staat
        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

        $title = 'Frisse Start: Seizoen 2 Sprint (30 Dagen) - Eerlijke Strijd, Helptool & Het Doel van het Spel!';
        $slug = Str::slug('frisse-start-seizoen-2-sprint-30-dagen');

        $content = <<<'MARKDOWN'
Beste patiënten, bendeleiders en rivalen,

De afgelopen dagen liepen de spanningen en emoties in de kliniek hoog op. Er ontstonden felle discussies tussen beide rivaliserende kampen over scheve levelverschillen, bende-inkomsten en repetitieve liquidaties. Als beheer hebben we intensief geluisterd naar alle spelers: van Arjan en Caroline tot Bossie, Dennis en de voltallige bende Bad Intentions. 

In plaats van doormodderen in een scheefgegroeide situatie, kiezen we vandaag voor een moedige en eerlijke stap: **we trekken een streep onder het verleden en geven iedereen een 100% frisse start!** 

Seizoen 2 gaat van start als een razendspannende **Sprint van exact 30 dagen (1 maand)**. Iedereen begint zij aan zij vanaf Level 1 met gelijke kansen, op basis van de vernieuwde, uitgebalanceerde spelmechanieken.

---

## 🧭 Wat is het Echte Doel van TBS Kliniek?

Met alle commotie rondom liquidaties leek het soms alsof het spel alleen maar draait om willekeurig schieten. Dat is **niet** waar TBS Kliniek voor bedoeld is!

> [!IMPORTANT]
> **Het Ultieme Doel van TBS Kliniek:**
> TBS Kliniek is een diepe misdaad- en gevangenis-RPG. Het doel is om vanuit een straatarme nieuwkomer op te klimmen tot de meest gevreesde, rijkste en gerespecteerde eindbaas van de hele inrichting!
>
> Dat doe je via meerdere sterke pijlers:
> 1. **Bendes & Vastgoed:** Richt een bende op, koop syndicaatpanden, beheer wagenparken en verdien elk uur passief bende-uurloon.
> 2. **Grote Heists & Kluizen:** Bereid met je crew overvallen voor en kraak de Centrale Medicijnkluis.
> 3. **Handel & Garage:** Koop opknappers, tune motoren en ruil wagens met winst in bij de dealer.
> 4. **Karakterontwikkeling:** Train je Kracht, Verdediging en Discipline in het Krachthonk en werk noest in de Dagbesteding.
> 5. **Territorium & Oorlog:** Strijd om kliniekzones in 24-uurs bendeoorlogen.
>
> 🎯 **Liquidatie is een tactisch oorlogswapen, géén doel op zich!**  
> Schieten met kogels is bedoeld om in officiële bendeoorlogen zones te veroveren of om via Bloedwraak gerichte vergelding te nemen op een rivaal. Het is nooit bedoeld geweest als een hersenloze klikgrind om willekeurige spelers de hele dag plat te leggen.

---

## 📖 Gebruik de Vernieuwde Helptool (Gids & FAQ)!

Om het spel optimaal te benutten en niet voor verrassingen te komen te staan, roepen we iedereen met klem op om de vernieuwde **[Helptool & FAQ](/faq)** te raadplegen.

In de Helptool vind je:
- Uitgebreide handleidingen voor beginners tot pro-endgame;
- De **Interactieve Gevecht Simulator** waarin je vooraf precies berekent of je een gevecht kunt winnen;
- Uitleg over alle 30 Dossier-Graden, Koudbloedigheid en het dodelijke 1-Patroon Genadeschot;
- Complete tabellen van bende-inkomsten, pandproducties en garage-vlooteisen;
- Heldere antwoorden op meer dan 80 veelgestelde vragen!

Neem de tijd om de gids door te nemen: kennis is macht in de TBS Kliniek!

---

## ⚖️ Alle Spelmechanieken van Seizoen 2 op een Rij

Om te zorgen dat de strijd eerlijk, competitief en bloedstollend spannend blijft, gelden vanaf vandaag de volgende definitieve spelregels:

### 1. Gelijker Levelen & Einde aan de Oneindige Trein
- **Geen oneindige energie-refill meer:** Een level-up geeft nu een gerichte bonus van +25 energie in plaats van een complete 100/100 navulling. Hierdoor kan niemand meer eindeloos doortreinen in de gym.
- **Maximaal 1 level per actie:** Meerdere levels tegelijk overslaan met één klik is definitief geblokkeerd.
- **Compacte Top:** Boven level 35 schaalt de benodigde ervaring veel steiler omhoog. De top van de server blijft daardoor compact bij elkaar (tussen level 40 en 50), zodat iedereen binnen elkaars 15-level gevechtsbereik blijft en niemand meer eenzaam geïsoleerd raakt!
- **Inhaalbonus (+20% XP):** Spelers onder level 20 ontvangen automatisch +20% bonus-XP op al hun activiteiten om vlot aan te sluiten bij het middenveld.

### 2. Tactisch Schieten (Liquidatie 2.0)
- **Daglimiet van 5 liquidaties:** Je kunt per dag maximaal 5 willekeurige liquidaties uitvoeren. Bewaar je kogels voor het echte werk!
- **Anti-Pestslot (Max 3 hits per doelwit):** Het is technisch onmogelijk om dezelfde speler meer dan 3 keer op één dag te liquideren.
- **Onbeperkt Vechten bij Bendeoorlog & Bloedwraak:** Zodra bendes een officiële 24-uurs oorlog uitroepen of Bloedwraak innen, vervalt de daglimiet en kan er voluit gestreden worden om territorium en eer!
- **Vergeldingsslot (Dennis-Model):** Buiten de 15 levels mag je bij oorlog of bloedwraak maximaal 1x per uur op dat specifieke doelwit schieten.
- **Anti-Hit-and-Run (30 min gevechtsstatus):** Wie aanvalt of schiet, raakt 30 minuten in gevechtsstatus en kan géén vrijwillige isolatie kopen.
- **Buit-Plafond van €25.000.000:** Een voltreffer rooft maximaal 45% van het contante zakgeld met een hard plafond van 25 miljoen euro. Bankgeld en kluis blijven altijd 100% veilig!

### 3. Contant Bende-Uurloon (Spanning & Geen Vanilla)
- Bende-uurlonen worden direct contant op zak uitbetaald. TBS Kliniek is een echte misdaadgame en geen risicoloze vanilla klikker: zorg dus dat je alert blijft en je verdiende contanten op tijd veilig naar De Bank brengt!

### 4. Bende Panden, Garage & Specialist NPC's
- **Smokkelkiosken (Stabiele Basis, max 1.000):** Kiosken leveren een vaste bijdrage van €50 per kiosk per uur (max €50.000 per uur).
- **Actief Bende Wagenpark:** Voertuigen in de Bende Garage rijden logistieke diensten (€1.500/u per motor, €4.000/u per auto, €12.500/u per boot, €25.000/u per helikopter/vliegtuig). Schade aan deze voertuigen wordt 100% betaald uit de gezamenlijke bendekas.
- **Syndicaat Panden (tot €1,5 miljard):** Start lucratieve productieorders met doorlooptijden van 2,5 tot 8 uur.
- **Bende Heists met NPC Handlangers:** Kleine en middelgrote bendes kunnen ingehuurde NPC specialisten meenemen naar grote overvallen. Zij tellen volwaardig mee voor de minimale bemanningseis van de Heist! 50% van de buit gaat direct contant naar jou, 50% naar de bendekas.

### 5. Wagenhandel & Nieuwe Ranglijsten
- In de Smokkel Garage kunnen spelers schadeauto's opknappen en direct inruilen bij de dealer voor keihard bankgeld en +40 handels-XP.
- Op de Ranglijst staan 4 nieuwe categorieën voor Modelpatiënten (meeste werkdiensten), Discipline Meesters, Missie Bazen en Meester Hackers.

---

## 🏆 Wat Gebeurt er met je Huidige Spullen?

Tijdens deze seizoensreset:
- **BEHOUDEN:** Jouw account, gebruikersnaam, bende (naam & leden), alle Gouden Sterren, alle VIP-punten, jouw actieve VIP-dagen, alle pompoenpitten, de Herfstquest-voortgang én de lopende weekchallenge tot vrijdag!
- **GERESET:** Levels (terug naar 1), statistieken (terug naar 10), contant geld (€250) en bankgeld (€1.000), wagens, panden en openstaande bloedwraaks.

Over exact **30 dagen** sluit Seizoen 2 af met een gigantische finale waarin de top van de ranglijsten en de machtigste bende eeuwige roem en spectaculaire seizoensprijzen winnen!

Veel succes, strijd eervol en maak er een legendarisch seizoen van!
MARKDOWN;

        $article = NewsArticle::create([
            'character_id' => $authorId,
            'title' => $title,
            'slug' => $slug,
            'emoji' => '👑',
            'image_url' => null,
            'content' => $content,
            'is_pinned' => true,
            'comments_enabled' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->info("Nieuw allesomvattend update-artikel succesvol geplaatst! ID: {$article->id}, Slug: {$article->slug}");

        // Zorg ook dat het Hossel Challenge artikel gesynchroniseerd en gepubliceerd is
        \App\Services\EventHustleChallengeService::syncChallengeArticle();
        $this->info("Hossel Challenge artikel gesynchroniseerd.");

        return self::SUCCESS;
    }
}
