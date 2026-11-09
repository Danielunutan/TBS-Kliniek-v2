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
#[Description('Publiceert de grote officiële update nieuwsbrief met uitleg over alle nieuwe systemen')]
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
            'grote-update-alles-wat-nieuw-is-in-tbs-kliniek',
        ])
            ->orWhere('title', 'like', '%Roterende Garage en Grote Bende Heists%')
            ->orWhere('title', 'like', '%Grote Update: Nieuw Bende-Uurloon%')
            ->delete();

        $this->info("Vorige update artikelen opgeruimd ({$deleted} verwijderd)");

        // Pinned status beheren zodat dit artikel bovenaan staat
        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

        $title = 'Grote Update: Alles over het Nieuwe Bende-Uurloon, Vastgoed, Garage Vloot & PvP!';
        $slug = Str::slug('grote-update-alles-wat-nieuw-is-in-tbs-kliniek');

        $content = 'Beste patiënten en bendeleden,

De afgelopen tijd is TBS Kliniek op vrijwel elk vlak enorm uitgebreid en gemoderniseerd. Of je nu net voor het eerst een cel binnenstapt of al maanden aan het hoofd staat van een zwaarbewapend syndicaat: het spel zit vol krachtige nieuwe mogelijkheden om geld te verdienen, oorlog te voeren en een imperium op te bouwen.

Omdat er zoveel tegelijk is toegevoegd, vind je hieronder het complete overzicht van **ALLES** wat nieuw is, stap voor stap en helder uitgelegd voor zowel beginners als ervaren rotten!

---

## 💰 1. Het Vernieuwde Bende-Uurloon Systeem

### Wat is het Bende-Uurloon? (Voor Beginners)
Als lid van een bende ontvang je elk heel uur automatisch een salaris op je bankrekening zolang je actief bent. Je hoeft hiervoor niets te doen: het is een passief inkomen dat jouw loyaliteit aan je bende beloont.

### Wat is er veranderd?
Voorheen liepen uurloonbedragen uit de hand door oneindige kioskaankopen. Vanaf nu is het uurloon gebaseerd op een gezonde, tastbare en transparante economie. Jouw bende verdient geld met echte bezittingen:

> [!IMPORTANT]
> **Smokkelkiosken Limiet (1.000 max) & 100% Geld-Restitutie:**
> Om de bende-economie voor iedereen transparant en eerlijk te houden, geldt er vanaf nu een officiële limiet van **maximaal 1.000 Smokkelkiosken per bende**.
> - Iedere kiosk levert een vaste verhoging van **+€50 per uur** op aan het bende-basisloon (maximaal €50.000 per uur bij 1.000 stuks). Geen ingewikkelde staffels meer!
> - **100% Restitutie:** Alle bendes die meer dan 1.000 kiosken bezaten, hebben voor elke overtollige kiosk de volledige aankoopwaarde van **€50.000 per stuk** direct teruggestort gekregen in hun gezamenlijke bendekas!
> - Hierdoor hebben bendes honderden miljoenen tot ruim 1,6 miljard euro aan verse liquiditeit teruggekregen, direct inzetbaar voor de 10 nieuwe panden en garagevloten!

- **Smokkelkiosken (Stabiele Basis, max 1.000):** Kiosken geven een betrouwbare basisinkomst van **€50 per kiosk per uur** tot het maximum van 1.000 stuks (maximaal €50.000 per uur). Zo hebben beginnende en groeiende bendes altijd een stabiele bodem.
- **Actief Bende Wagenpark (24/7 Inkomsten):** Elk voertuig dat in de Bende Garage staat gestald, rijdt logistieke diensten voor de organisatie en genereert elk uur geld:
  - Motoren: €1.500 per uur per motor
  - Auto\'s: €4.000 per uur per auto
  - Boten: €12.500 per uur per boot
  - Helikopters en Vliegtuigen: €25.000 per toestel per uur
- **Bende Panden & Vastgoed:** Elk aangeschaft pand levert een forse vaste uurloonbijdrage op plús een permanente percentagebonus (tot wel +40% per topfaciliteit).
- **Bende Respect Bonus:** Hoe hoger het totale respect van jouw bende, hoe hoger de procentuele bonus over de totale pot (+1% per 100.000 respect tot maximaal +50%).
- **Heerschappij Bonus (+25%):** Wint jouw bende een officiële 24-uurs bendeoorlog? Dan geniet de hele bende 7 dagen lang van een extra +25% bonus bovenop het hele uurloon!

### Eerlijke Salarisschaal per Rang
De bende-loonpot wordt automatisch verdeeld over de leden naar gelang hun rang en inzet:
- Recruut: 50% van het basisloon
- Bendelid: 100% van het basisloon
- Veteraan: 110% van het basisloon
- Onderbaas: 125% van het basisloon
- Co-Leader: 140% van het basisloon
- Bendeleider: 150% van het basisloon

Op de [Bende Uurloon](/gangs/uurloon) pagina vind je nu een live calculator waarin je realtime ziet waar elke euro in de pot vandaan komt!

---

## 🚗 2. De Bende Garage: Reviseren via Bendekas & Wagens Doneren

In de [Bende Garage](/gangs/garage) brengt de bende een wagenpark bijeen voor grootschalige operaties. Dit systeem is flink verbeterd:

- **Reviseren 100% via de Bendekas:** Schade aan voertuigen in de bende garage wordt vanaf nu **volledig betaald uit de gezamenlijke bendekas**. Het persoonlijke geld en de privobankrekening van leden worden nooit meer aangeraakt! Is de kas leeg? Dan moet de leiding eerst bijstorten voordat monteurs aan de slag gaan.
- **Wagens Definitief Doneren (+50 Respect):** Leden kunnen nu hun wagens permanent schenken aan het centrale bende-wagenpark via de knop "Doneer Definitief aan Bende".
  - De donateur ontvangt direct **+50 Respect** (wat ook het benderespect direct omhoog stuwt!).
  - Het voertuig krijgt de badge **Bende Eigendom**. De wagen blijft voor altijd eigendom van de bende en kan niet meer door individuele leden worden teruggehaald naar de privéstalling.
- **Wagens Slopen voor Bendekas:** Als de garage vol raakt, kunnen bendeleiders en co-leiders overtollige bende-eigendom wagens ontmantelen via de rode knop. Dit levert direct 25% van de aanschafwaarde op in contanten voor de bendekas!

---

## 🏭 3. Bende Panden: 10 Nieuwe Mega-Panden, Productie & Vlooteisen

Bendes kunnen in [Bende Panden](/gangs/panden) vastgoed aankopen vanuit de gezamenlijke bendekas:

- **10 Nieuwe Top-End Syndicaat Panden:** Er zijn 10 nieuwe panden toegevoegd met aanschafprijzen van €15 miljoen tot maar liefst €1,5 miljard voor miljardairs-syndicaten (van Drone Smokkelbases en Speedboot Dokken tot Diamantslijperijen en Internationale Kartel Citadels).
- **Productiecycli Draaien:** Leiders kunnen in de panden grootschalige productieorders starten (met doorlooptijden van 2,5 tot 8 uur). Na voltooiing incasseert de bende miljoenen aan zwart geld en duizenden respectpunten!
- **Verplichte Garage-Vlooteisen:** Let goed op: geavanceerde panden vereisen operationele wagens in de Bende Garage (zoals minimaal 3 snelle wagens van minstens 80 km/u of meerdere helikopters). Zonder de juiste vloot kan de productie niet starten; de knop toont dan direct welke voertuigen je bende nog mist.
- **Roterende Showroom:** Elk half uur toont de makelaar een wisselend aanbod van panden verdeeld over vier eerlijke prijsklassen.

---

## 🧑‍🔬 4. Bende Specialist NPC\'s (30-Minuten Contracten)

Bendes kunnen nu gespecialiseerde hulpkrachten inhuren om hun producties en veiligheid te maximaliseren:
- Huur chemici, witwassers en zwaarbewapende bewakers in voor een contract van 30 minuten per productiecyclus.
- Een handige live countdown toont precies wanneer het contract afloopt. Specialisten verhogen de opbrengst, verkorten de productietijd en beschermen je pand tegen invallen!

---

## 💎 5. Bende Co-Op Heists (Grote Overvallen)

Op het [Heist Planningsbord](/heists) staan nu officiële Bende Heists klaar (zoals de Douane Depot Kraak en het Meesterwerk: Fort Goudkluis Heist):
- **Eerlijke 50/50 Buitverdeling:** Bij een geslaagde overval ontvang jij 50% van de buit direct contant op zak. De overige 50% belandt automatisch in de gezamenlijke bendekas!
- **Bende Respect:** Elke kraak levert duizenden extra respectpunten op voor de gang.
- **Co-Op Bonus:** Hoe meer actieve leden jouw bende heeft, hoe hoger de gezamenlijke slagingskans.
- **Vluchtwagen Vereiste:** Voor zware heists moet er een voertuig in de Bende Garage staan met voldoende topsnelheid (bijvoorbeeld minimaal 95 km/u voor Fort Goudkluis).

---

## ⚔️ 6. 24-Uurs Bendeoorlogen om Kliniek Zones & Bende Wraakrecht

Het bendegeweld heeft een geheel nieuw strategisch niveau gekregen:
- **24-Uurs Zone Gevechten:** Bendes kunnen de strijd aangaan om belangrijke kliniekzones (de Kantine, Luchtplaats, Apotheek en meer). De oorlog duurt 24 uur; de bende met de meeste gevechtspunten wint de zone.
- **Heerschappij Beloning:** De heersende bende incasseert 7 dagen lang een gigantische +25% bonus op het bende-uurloon!
- **Bende Wraakrecht (Retaliation):** Valt iemand een lid van jouw bende aan? Dan krijgt jouw hele bende officieel wraakrecht op die aanvaller. Hierbij worden alle normale level-beperkingen tijdelijk omzeild, zodat je samen met je zwaarste bendeleden vergelding kunt nemen!

---

## 🏛️ 7. Cel Upgrades (Jouw Persoonlijke Verblijf)

Patiënten kunnen nu hun eigen gevangeniscel grondig verbouwen en upgraden:
- Investeer in luxe celmeubilair, beveiligde verstopplekken, trainingshoeken en comfort.
- Celupgrades bieden status, beschermen je tegen plotselinge invallen en geven handige persoonlijke voordelen.

---

## 🏎️ 8. De Roterende Smokkel Garage (Wagenhandel & Opknappers)

In de centrale [Smokkel Garage](/garage) arriveert elk half uur (om het hele en halve uur) een nieuwe levering van 8 wagens:
- **Schadebolides & Opknappers:** Voertuigen worden aangeboden met wisselende condities. Een beschadigd voertuig koop je voor een bodemprijs, repareer je voordelig in je werkplaats en zet je met dikke winst door of doneer je aan je bende.
- **Fabriekstuning:** Sommige wagens zijn uitgerust met getunede motoren voor extra topsnelheid die blijvend behouden blijft.
- **Alle Voertuigklassen:** Het wagenpark telt nu 28 unieke voertuigen, waaronder snelle scooters, quads, kogelwerende sedans, smokkelboten en vluchthelikopters die allemaal ingezet kunnen worden bij heists en reizen!

---

## 🥊 9. PvP Diefstallen & Dagelijkse Limieten

Om de economie spannend en eerlijk te houden zijn er heldere dagelijkse limieten ingesteld voor het bestelen en aanvallen van medespelers:
- Reguliere patiënten kunnen maximaal 100 PvP-aanvallen/diefstallen per dag uitvoeren.
- VIP-leden profiteren van een verhoogde limiet van maar liefst 1.000 acties per dag!

---

## 🛡️ 10. Medisch Schild Beveiliging

Als je net uit de ziekenboeg komt, ben je beschermd door een Medisch Schild. 
- Val je zelf iemand aan terwijl je schild actief is? Dan verschijnt er voortaan eerst een duidelijke pop-up waarschuwing met bevestigingsvraag.
- Zo verlies je nooit meer per ongeluk je bescherming door een misklik!

---

## 🏦 11. Banktransacties tot 1 Biljoen Euro (€1.000.000.000.000)

De database van de kliniekbank is volledig gemoderniseerd naar 64-bit getallen. 
- Spelers en bendes liepen vroeger vast op de oude 2 miljard limiet. Vanaf nu kun je probleemloos honderden miljarden storten, overmaken en opnemen zonder dat bedragen overstromen of vastlopen.

---

## 📈 12. Dynamische Level-Scaling voor Werk en Misdaden

Het werk- en misdaadsysteem groeit nu dynamisch mee met jouw personage:
- Naarmate je een hoger level bereikt, ontgrendel je geavanceerde endgame tiers voor banen en kliniekklusjes.
- Hogere levels leveren aanzienlijk meer geld, respect en ervaringspunten (XP) op, zodat werken en hosselen ook voor topspelers altijd uitdagend en lucratief blijft!

---

## 🌟 Gouden Tips voor Beginnende Spelers

Ben je nieuw in TBS Kliniek? Volg deze vier stappen om razendsnel een vermogen op te bouwen:
1. **Sluit je direct aan bij een Bende:** Bezoek de [Bende Pagina](/gangs) en word lid. Je ontvangt direct elk uur automatisch bende-uurloon!
2. **Scoor een Goedkope Wagen:** Bezoek de [Smokkel Garage](/garage) en tik een goedkope opknapper op de kop voor je eerste solo klusjes.
3. **Versterk het Bende Wagenpark:** Doneer wagens die je over hebt aan de bende voor direct +50 Respect en help je gang om de zware productiecycli te kunnen draaien.
4. **Vecht Mee in Bendeoorlogen:** Help je makkers om kliniekzones te veroveren voor de gigantische +25% heerschappij-bonus over het hele uurloon!

Heb je vragen of opmerkingen? Deel je reactie hieronder of praat gezellig mee in de Live Chat!';

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

        return self::SUCCESS;
    }
}
