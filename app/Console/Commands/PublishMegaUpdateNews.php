<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('tbs:publish-mega-update')]
#[Description('Publiceert het officiële mega update nieuwsbericht van vandaag over Halloween, Bende Panden, Marktplaats en Krachttraining')]
class PublishMegaUpdateNews extends Command
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

        // Fallback als Daniel nog niet gevonden is
        if (! $danielCharacter) {
            $danielCharacter = Character::first();
        }

        $authorId = $danielCharacter?->id;
        $this->info("Auteur geselecteerd: ID {$authorId} ({$danielCharacter?->name})");

        // Eventueel eerdere pinned artikelen unpinnen zodat deze bovenaan staat
        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

        $title = '🎃 Mega Update: Halloween Event 2026, Bende Panden, Marktplaats & Krachttraining!';
        $slug = Str::slug('mega-update-halloween-bende-panden-marktplaats-krachttraining');

        $content = 'Beste patiënten en bendeleden,

Vandaag rollen we een gigantische reeks updates en kwaliteitsverbeteringen uit over heel TBS Kliniek! Van het officiële Halloween Event 2026 tot een compleet vastgoedimperium voor bendes, uitbreiding van de centrale marktplaats en een compleet gemoderniseerde krachttraining.

[credit=therainbowuniverse2]Enorm bedankt voor de creatieve ideeën en feedback voor de Bende Panden, het Halloween Event en de uitbreiding van de Marktplaats![/credit]

[credit=ozzy]Dank voor de waardevolle feedback over de krachttraining flow en de wens voor een snellere, vloeiende upgrade-ervaring![/credit]

---

## 🎃 1. Halloween Event 2026: Het Spookseizoen is Begonnen!
De gangen van de TBS Kliniek zijn gehuld in een griezelige nevel. Het officiële Halloween Event is nu live voor alle patiënten!

- **Halloween Quests:** Trotseer 7 duistere missies met oplopende beloningen, van mysterieuze nachtelijke dwalers tot het opgraven van verborgen kliniekgeheimen.
- **Pompoenpitten (🎃):** Verzamel tijdens je missies en activiteiten de tijdelijke seizoensvaluta.
- **Exclusieve Spook-Shop:** Wissel je pompoenpitten in voor griezelige avatar frames (zoals *Vleermuis Gloed*, *Spinnenweb Gothic*, *Spookachtige Nevel* en *Heksengroen Gif*), tijdelijke energie- en krachtboosts en unieke seizoens-items.
- **Duistere Kliniek-Vibe:** De hele Halloween-omgeving sluit nu naadloos aan op onze duistere kliniekhuisstijl zonder storende witte vlakken.

Je vindt het evenement direct via het hoofdmenu onder **[Halloween Event](/halloween)**!

---

## 🏢 2. Bende Panden & Vastgoed Imperium
Bendes kunnen hun macht en invloed nu definitief vestigen met hun eigen vastgoedportefeuille. Vanuit de bendekas kunnen leiders en medeleiders exclusieve panden aankopen en exploiteren:

- **Ondergrondse Loods:** Vergroot de opslag- en smokkelcapaciteit en biedt een veilige haven voor bendebuit en materieel.
- **Valsgeld Drukkerij & Chemische Fabriek:** Genereer doorlopende passieve inkomsten en grondstoffen voor de bendekas.
- **Zwaarbeveiligde Gang Villa:** Verhoog het bende-respect, verstevig je verdediging en breid het maximale ledenaantal van jouw organisatie uit.
- **Specialist NPC\'s:** Rekruteer chemici, witwassers en zwaarbewapende huurlingen om de productie en verdediging van je panden naar een hoger niveau te tillen.

Ga samen met je bende naar **[Bende Panden](/gangs/panden)** en begin met bouwen aan jullie imperium.

---

## 🏷️ 3. Centrale Marktplaats Uitbreiding
De handel tussen patiënten onderling is flink uitgebreid. De Marktplaats is nu dé centrale plek voor vrije handel:

- **Depot Items & Buit:** Verkoop zeldzame buit, smokkelwaar en verbruiksartikelen direct aan andere spelers.
- **Voertuigen & Garage Bolides:** Verkoop je getunede wagens, motoren en smokkelvoertuigen door tegen je eigen vraagprijs.
- **Bende Panden:** Zelfs eigendomsrechten op panden kunnen nu veilig via de marktplaats verhandeld worden.
- **Directe & Veilige Afhandeling:** Met handige zoekfilters, duidelijke categorieën en automatische escrow-beveiliging.

Breng direct een bezoek aan de **[Marktplaats](/marketplace)** om de nieuwste koopjes te ontdekken of je eigen handel aan te bieden.

---

## 💪 4. Krachttraining: Vloeiend & Razendsnel (+5x Upgrade)
Op verzoek van de community is de krachttraining grondig aangepakt voor een veel fijnere speelervaring:

- **Directe In-Place Training:** Trainen gebeurt nu vloeiend zonder dat de hele pagina opnieuw hoeft te laden of onnodig verspringt.
- **Nieuwe +5x Snelupgrade Knop:** Train in één klik meerdere herhalingen tegelijk voor snelle progressie.
- **Realtime Bovenbalk Sync:** Je actuele energie, respect en statistieken lopen direct synchroon mee met elke trainingssessie.

Ga naar **[Krachttraining](/training)** en pomp die spieren op voor het volgende gevecht!

---

## 🛠️ 5. Kwaliteitsverbeteringen & Balans
Naast grote features hebben we vandaag ook diverse verfijningen doorgevoerd:

- **Social Rewards:** Beloningen voor het volgen en koppelen via Instagram en Hyves werken nu vlekkeloos.
- **Diefstallen & Gevangenis Balans:** Kansberekeningen en beloningen voor zakkenrollen en ontsnappingen zijn opnieuw gekalibreerd.
- **Support Systeem Opgeruimd:** Beantwoorde tickets hebben een nette, overzichtelijke layout gekregen met ondersteuning voor rijke tekst en directe statusoverzichten.

We wensen iedereen veel speelplezier en een spannend griezelseizoen toe in TBS Kliniek!

Heb je feedback of suggesties? Deel het in de reacties hieronder of open een ticket bij het kliniekpersoneel.';

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $authorId,
                'title' => $title,
                'emoji' => '🎃',
                'image_url' => null,
                'content' => $content,
                'is_pinned' => true,
                'comments_enabled' => true,
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        $this->info("Nieuwsbericht succesvol geplaatst! ID: {$article->id}, Slug: {$article->slug}");

        return self::SUCCESS;
    }
}
