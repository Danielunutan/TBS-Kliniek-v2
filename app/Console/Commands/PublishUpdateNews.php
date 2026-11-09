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
#[Description('Verwijdert het vorige update-artikel en publiceert de nieuwe uitgebreide, beginnersvriendelijke gids')]
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

        // 2. Verwijder het vorige update-artikel
        $deleted = NewsArticle::whereIn('slug', [
            'update-roterende-garage-en-grote-bende-heists',
            'grote-update-bende-uurloon-vastgoed-imperium-garage-vloot-bende-heists',
        ])
            ->orWhere('title', 'like', '%Roterende Garage en Grote Bende Heists%')
            ->delete();

        $this->info("Oude update artikelen opgeruimd ({$deleted} verwijderd)");

        // Eventueel andere pinned artikelen unpinnen zodat deze bovenaan staat
        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

        $title = 'Grote Update: Nieuw Bende-Uurloon, Vastgoed Imperium, Garage Vloot & Bende Heists!';
        $slug = Str::slug('grote-update-bende-uurloon-vastgoed-imperium-garage-vloot-bende-heists');

        $content = 'Beste patiënten en bendeleden,

De onderwereld van TBS Kliniek heeft de afgelopen dagen een complete transformatie ondergaan! Of je nu een doorgewinterde bendeleider bent met een kluis vol miljarden of een gloednieuwe patiënt die net zijn eerste stappen zet op het kliniekterrein, deze mega-update brengt enorme kansen voor iedereen.

In dit overzicht leggen we alle nieuwe systemen rustig en stap voor stap uit, zodat elke speler precies weet hoe je het maximale uit je bende, je wagenpark en je inkomsten kunt halen!

[credit=Rainbow]Dank aan speler Rainbow voor het waardevolle meedenken over het roterende garagesysteem en de bendevloot-mechanica.[/credit]

---

## 💰 1. Het Nieuwe Bende-Uurloon (Eerlijk, Transparant en Lucratief)

### Wat is het Bende-Uurloon? (Voor Beginners)
Als lid van een bende ontvang je elk heel uur automatisch een salaris op je bankrekening zolang je actief bent. Je hoeft hiervoor niet continu online te klikken; het is een passief inkomen dat jouw loyaliteit aan je bende beloont!

### Waarom is het systeem vernieuwd?
Vroeger ontspoorde het uurloon door oneindige kiosken in onoverzichtelijke bedragen. Vanaf nu is het bende-uurloon gebaseerd op een gezonde, tastbare economie. Jouw bende bouwt een echt imperium op met kiosken, wagens, syndicaatpanden en respect!

### De 5 Pijlers van de Bende Loonpot:
- **Kantine Smokkelkiosken:** Kiosken bieden een stabiele basis tot maximaal 250 effectieve kiosken (€100 per kiosk per uur = maximaal €25.000 per uur als betrouwbare bodem).
- **Actief Bende Wagenpark:** Voertuigen die in de Bende Garage gestald staan leveren 24/7 een logistiek uurrendement op voor de bende:
  - Motoren: €1.500 per uur per motor
  - Auto\'s: €4.000 per uur per auto
  - Boten: €12.500 per uur per boot
  - Helikopters en Vliegtuigen: €25.000 per toestel per uur
- **Bende Panden & Vastgoed:** Elk aangeschaft syndicaatpand levert een forse vaste uurloonbijdrage plus een percentagebonus (tot wel +40% per high-end pand).
- **Bende Respect Bonus:** Hoe hoger het totale respect van jouw bende, hoe hoger de procentuele toeslag (+1% per 100.000 respect tot maximaal +50%).
- **Heerschappij Bonus (+25%):** Wint jouw bende een officiële bendeoorlog? Dan geniet de hele bende 7 dagen lang van een extra +25% heerschappij-bonus over de totale pot!

### Hoeveel verdien jij persoonlijk? (Salarisschaal op Rang)
De totale loonpot van de bende wordt eerlijk verdeeld over de leden op basis van rang en inzet:
- Recruut: 50% van het basisloon
- Bendelid: 100% van het basisloon
- Veteraan: 110% van het basisloon
- Onderbaas: 125% van het basisloon
- Co-Leader: 140% van het basisloon
- Bendeleider: 150% van het basisloon

Op de [Bende Uurloon](/gangs/uurloon) pagina vind je nu een volledig transparant overzicht waarin je realtime ziet waar elke euro in de pot vandaan komt!

---

## 🚗 2. De Bende Garage: Reviseren via Bendekas & Wagens Doneren

### Revisies 100% Betaald uit de Bendekas
Staatsvoertuigen in de [Bende Garage](/gangs/garage) worden intensief gebruikt voor heists en producties. Daarom geldt vanaf nu: **alle reparaties en revisies van wagens in de bende garage worden direct en uitsluitend betaald uit de gezamenlijke bendekas!**
- Jouw eigen privégeld en persoonlijke bankrekening worden nooit meer aangeraakt.
- Heeft de bendekas te weinig saldo? Dan vraagt het systeem de leiding om eerst de kas aan te vullen.

### Wagens Definitief Doneren (+50 Respect)
Wil je jouw bende vooruithelpen? Leden kunnen nu persoonlijke wagens permanent schenken aan het centrale bende-wagenpark:
- Klik in de garage op **Doneer Definitief aan Bende** en bevestig je overdracht.
- Als dank ontvang je als donateur direct **+50 Respect** (wat ook direct de rang van je bende versterkt!).
- Gedoneerde wagens krijgen de paarse badge **Bende Eigendom**. Ze blijven voor altijd in de bende garage ten behoeve van de hele organisatie en kunnen niet meer door individuele leden worden teruggehaald.

### Slopen voor Bendekas
Wordt de garage te krap? Bendeleiders en co-leiders kunnen gedoneerde wagens via de rode knop ontmantelen. Dit levert direct 25% van de aanschafwaarde op in contanten voor de bendekas!

---

## 🏭 3. Bende Panden: 10 Nieuwe Panden & Productie Cycli

### Wat zijn Bende Panden? (Voor Beginners)
Bende Panden zijn strategische vastgoedobjecten die de bende aanschaft vanuit de gezamenlijke bendekas. Ze versterken de verdediging, leveren flinke uurloonbonussen op en stellen de bende in staat om zelf grootschalige goederen en zwart geld te produceren!

### 10 Nieuwe Top-End Syndicaat Panden
De vastgoedmarkt is uitgebreid met 10 gloednieuwe faciliteiten met aanschafprijzen variërend van €15 miljoen tot €1,5 miljard voor de rijkste syndicaten:
1. **Autonome Drone Smokkelbasis & Radartoren** (€15M)
2. **Digitale Spionage & Politie Aftapcentrale** (€30M)
3. **Nachtelijke Straatrace Syndicate & Tuner Hub** (€55M)
4. **Zwaar Geldtransport & Pantserwagen Depot** (€85M)
5. **Diepzee Speedboot Dok & Smokkelloods** (€140M)
6. **Militair Surplus Depot & Wapenmagazijn** (€220M)
7. **Geheim Offshore Vliegveld & Hangar Complex** (€350M)
8. **Hoogbeveiligde Diamantslijperij & Goudsmelterij** (€550M)
9. **Industriële Super-Synthese Raffinaderij** (€850M)
10. **Internationaal Kartel Distributiefort & Citadel** (€1,5 Miljard)

### Productie Starten & Vlootvereisten
Leiders en medeleiders kunnen in [Bende Panden](/gangs/panden) productieopdrachten starten (met cycli van 2,5 tot 8 uur). Bij voltooiing incasseert de bende miljoenen aan cash en duizenden respectpunten!
- **Belangrijke Vlooteis:** Veel geavanceerde panden vereisen operationele wagens in de Bende Garage om de productie te kunnen draaien (bijvoorbeeld minimaal 3 snelle wagens van minstens 80 km/u of meerdere helikopters).
- Voldoe je niet aan de vlooteis? Dan blijft de productieknop veilig vergrendeld met een directe link naar de garage zodat je eerst de juiste vloot kunt klaarzetten.
- **Roterende Showroom:** De makelaar toont elk half uur een gebalanceerd wisselend aanbod over 4 verschillende prijsklassen.

---

## 💎 4. Grote Bende Co-Op Heists (Samen Rijker Worden)

### Wat zijn Bende Heists? (Voor Beginners)
Op het [Heist Planningsbord](/heists) vind je naast individuele overvallen nu officiële Bende Heists. Dit zijn grootschalige krakers die je samen met je bende voorbereidt en uitvoert.

### Hoe werkt de buit en slagingskans?
- **Eerlijke 50/50 Buitverdeling:** Bij een geslaagde overval ontvang jij als overvaller 50% van de buit direct contant op zak. De overige 50% wordt automatisch bijgeschreven in de bendekas!
- **Bende Respect:** Elke geslaagde kraak levert duizenden respectpunten op voor de hele bende.
- **Co-Op Bonus:** Hoe meer actieve leden jouw bende telt, hoe hoger de slagingskans tijdens de overval.
- **Snelheidseis:** Let goed op de garage-vereiste van de heist! Voor zware operaties zoals de Fort Goudkluis Heist moet er minstens één wagen in de Bende Garage staan met een topsnelheid van minimaal 95 km/u.

---

## 🏎️ 5. De Roterende Smokkel Garage (Wagenhandel & Opknappers)

In de centrale [Smokkel Garage](/garage) arriveert elk half uur (om het hele en halve uur) een nieuwe lading van 8 unieke voertuigen.

### Handelen en Opknappen:
- **Wisselende Condities:** Voertuigen worden aangeboden in verschillende staten van onderhoud. Een beschadigde projectwagen koop je spotgoedkoop in, repareer je in je eigen werkplaats en verkoop je met winst door of doneer je aan je bende!
- **Fabriekstuning:** Sommige wagens arriveren met opgevoerde motoren en extra topsnelheid. Deze snelheidsbonus blijft permanent behouden.
- **28 Voertuigmodellen:** Van snelle brommers en quads tot kogelwerende sedans, smokkelboten en snelle helikopters.

---

## 🏦 6. Banktransacties Zonder Limieten

De kliniekbank is technisch geüpgraded naar 64-bit precisie. Spelers en bendes kunnen nu probleemloos enorme vermogens van tientallen of honderden miljarden overmaken, storten en beheren zonder vast te lopen op oude transactielimieten.

---

## 🌟 Gouden Tips voor Beginnende Spelers

Ben je net nieuw in TBS Kliniek? Volg deze stappen voor een supersnelle start:
1. **Zoek een Gezellige Bende:** Ga naar de [Bende Pagina](/gangs) en meld je aan bij een actieve bende. Je profiteert direct elk uur van het bende-uurloon!
2. **Scoor je Eerste Voertuig:** Bezoek de [Smokkel Garage](/garage), zoek een scherp geprijsde opknapper en voer je eerste solo heists uit.
3. **Help je Gang:** Doneer overtollige wagens aan de bende garage voor +50 Respect en help je bende de vlootvereisten voor grote producties te halen.
4. **Klim op in Rang:** Door actief te vechten, overvallen te plegen en bij te dragen aan de bende stijg je in rang en verdien je een steeds groter deel van de bende-loonpot!

Heb je vragen of ideeën over deze update? Laat hieronder gerust een reactie achter of praat mee in de Live Chat!';

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

        $this->info("Nieuw update-artikel succesvol geplaatst! ID: {$article->id}, Slug: {$article->slug}");

        return self::SUCCESS;
    }
}
