<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('tbs:publish-pvp-news')]
#[Description('Verwijdert het oude PvP nieuwsbericht en publiceert het uitgebreide Liquidatie 2.0 uitlegartikel')]
class PublishNewPvpNewsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $authorCharacter = Character::where('name', 'Daniel')
            ->orWhere('name', 'Daniël')
            ->orWhere('username', 'daniel')
            ->first();

        if (! $authorCharacter) {
            $danielUser = User::where('name', 'like', '%Daniel%')
                ->orWhere('email', 'like', '%daniel%')
                ->first();

            if ($danielUser && $danielUser->character) {
                $authorCharacter = $danielUser->character;
            }
        }

        if (! $authorCharacter) {
            $authorCharacter = Character::first();
        }

        $authorId = $authorCharacter?->id;

        // 1. Delete old PvP update articles
        $oldSlugs = [
            'grote-pvp-update-eerlijke-liquidatie-fair-play-bloedwraak',
            'grote-update-liquidatie-20-30-dossier-gradaties-3-uurs-backfire-vest-schaduw-inlichtingen',
            'grote-update-liquidatie-20-met-30-dossier-gradaties-en-3-uurs-backfire-vest',
            Str::slug('Grote PvP Update: Eerlijke Liquidatie, Fair Play & Bloedwraak'),
        ];

        $deletedCount = NewsArticle::whereIn('slug', $oldSlugs)
            ->orWhere('title', 'like', '%Eerlijke Liquidatie%')
            ->orWhere('title', 'like', '%Liquidatie 2.0%')
            ->delete();

        $this->info("Oude PvP nieuwsberichten verwijderd ({$deletedCount})");

        // 2. Create & Pin the detailed Liquidatie 2.0 guide without em-dashes, semicolons or colons
        $title = 'Volledige Uitleg Liquidatie 2.0 met Dossier Gradaties, Schaduwen en 3 Uurs Backfire Vest';
        $slug = Str::slug($title);

        $content = 'Beste patiënten en bendeleiders van de TBS Kliniek!

Het **Liquidatiesysteem (PvP)** is volledig vernieuwd naar **Liquidatie 2.0**. Geen saaie 1-uurs coma meer na één schot en niet meer blind vanuit elke kamer op mensen knallen, maar een echt kat-en-muisspel door de verschillende ruimtes van de kliniek!

In dit bericht leggen we **stap voor stap en heel duidelijk** uit wat elk onderdeel betekent en hoe het werkt.

---

### 🕵️ Stap 1. Doelwit Opsporen in de Kliniek (Schaduw-Inlichtingen)
**Wat is het?**
De TBS Kliniek bestaat uit 8 verschillende ruimtes (zoals *Paviljoen 1*, *De Luchtplaats*, *Het Kookhuis*, *De Recreatiezaal* en *De Buitenplaats*). Om op een speler te kunnen schieten, **moet je in exact dezelfde ruimte staan als je doelwit**. Standaard is de locatie van andere spelers geheim.

**Hoe vind je iemand?**
Op de pagina **Liquidatie** (onder tabblad **2. Schaduw-Inlichtingen**) of bij **Informanten** kun je corrupte bewaarders en cameratoezicht inschakelen om een speler op te sporen. Je hebt 3 keuzes die je met contant geld of bankgeld kunt betalen
- **1. Camera en Gangpad Schaduwing (€350.000 of 20 Sterren)**
  Het opsporen duurt **15 minuten**. Zodra de 15 minuten voorbij zijn, is je rapport **30 minuten lang geldig**. Je ziet dan live in welke ruimte je doelwit loopt, hoeveel contant geld en bankgeld hij heeft en welk vest hij draagt.
- **2. Hoofdbewaker Spoed-Dossier (€850.000 of 40 Sterren)**
  Versnelde opsporing die al na **7 minuten** klaar is en **45 minuten** geldig blijft.
- **3. Celblok-Lockdown en Schaduwing (€1.750.000 of 75 Sterren)**
  Duurt **15 minuten**, maar zodra het rapport binnen is gaan de deuren van de afdeling waar je doelwit staat **10 minuten lang op slot (Lockdown)**! Je doelwit kan dan niet wegvluchten naar een andere kamer terwijl jij onderweg bent.

---

### 🏃 Stap 2. Naar je Doelwit Reizen
Zodra je inlichtingenrapport na 15 minuten klaar is, verschijnt er bij het rapport direct een groene knop **Reis naar afdeling**.
- Met **1 klik** koop je de bewakers om en sta je direct in dezelfde kliniek-ruimte als je doelwit.
- Daarna druk je op **Direct Viseren** om het vuur te openen!

---

### 🎯 Stap 3. Hoeveel Patronen Moet Je Schieten? (Dossier-Graad en Koudbloedigheid)
Hoeveel kogels (patronen) je nodig hebt om iemand neer te schieten, hangt af van twee nieuwe eigenschappen op je Liquidatiepagina

**1. Wat is je Dossier-Graad (Graad 1 t/m 30)?**
Dit is jouw schuttersrang binnen de kliniek.
- Hoe stijgt je Dossier-Graad? Je graad wordt automatisch berekend op basis van je **Level** plus een bonus voor je gemaakte **Liquidatie-Kills** (elke 2 kills geeft +1 extra Dossier-Graad bovenop je level, tot maximaal Graad 30).
- **Graad 1 t/m 24 (Salvo Schieten)** Ben je nog tussen Graad 1 en 24? Dan ben je nog geen meesterscherpschutter en kun je **niet** met 1 kogel schieten. Je moet dan een flink salvo aan patronen afvuren. Op Graad 1 (*Observatiepatiënt*) heb je bijvoorbeeld minimaal **3.200 patronen** nodig, op Graad 10 (*Nachtdienst-Schrik*) **1.000 patronen** en op Graad 20 (*Dossier-Wisser*) **235 patronen**.
- **Graad 25 t/m 30 (Het 1-Patroon Genadeschot!)** Vanaf **Dossier-Graad 25 (*Forensisch Scherpschutter*)** ontgrendel je het **Genadeschot** waarmee je een doelwit met **slechts 1 patroon** kunt liquideren!

**2. Wat is Koudbloedigheid (0% tot 100%)?**
Dit is jouw kalmte en schietfocus.
- Om vanaf Graad 25 met **1 patroon** te kunnen schieten, moet je **Koudbloedigheid op minimaal 30%** staan!
- **Zakt je Koudbloedigheid onder de 30%?** Dan trilt je hand en kun je tijdelijk **niet** met 1 patroon schieten (je moet dan een normaal salvo schieten).
- Elke keer dat je schiet kost dat focus (een 1-patroon Genadeschot kost **-12% Koudbloedigheid**, een salvo kost **-6%**).
- **Hoe krijg je weer meer Koudbloedigheid?** Rechts op de Liquidatiepagina vind je de **Ondergrondse Schietbaan**. Voor **€350.000 en 15 Energie** train je direct **+10% Koudbloedigheid** erbij. Ook elke training bij **Vechtoefening** geeft **+4% Koudbloedigheid**, en grote munitiekisten geven direct een focus-bonus!

---

### 💰 Stap 4. Wat is de Buit bij een Geslaagde Liquidatie?
Als jouw schot sterker is dan de verdediging van je doelwit, is de liquidatie geslaagd!
- Je steelt direct **45% van het contante geld** dat het slachtoffer op zak heeft.
- Én je steelt direct **7% van het geld op de bankrekening** van het slachtoffer!
- Daarnaast krijg je **+60 Respect**, **+400 XP**, **+1 Kill** op je profiel en strijk je automatisch eventuele openstaande **Kopgeld-premies** en **Bloedwraak-bonussen** (€25.000 en +50 extra Respect) op.

---

### 🚑 Wat Gebeurt Er Als JIJ Wordt Geliquideerd? (Brancard-Evacuatie en 10/10 Code Blauw)
We hebben ervoor gezorgd dat jagers onbeperkt kunnen blijven jagen, maar dat slachtoffers **niet** urenlang vastzitten of niet kunnen healen

**Bij Liquidatie 1 t/m 9 (Direct Healen en Doorspelen!)**
- Word je neergeschoten? Dan kom je **niet** in een 1-uurs coma!
- Je houdt **20 HP** over en kunt **direct eten en medicijnen gebruiken om jezelf naar 100% HP te healen** en gewoon verder spelen.
- **Automatische Brancard-Evacuatie** Je wordt na het schot per brancard automatisch verplaatst naar een **willekeurige andere kliniek-ruimte** én je krijgt **15 minuten Medisch Infuus-Schild** (dit schild vervalt direct als je zelf iemand aanvalt).
- Wil dezelfde jager jou nóg een keer liquideren? Dat kan, maar hij weet niet naar welke kamer jouw brancard is gereden! Hij moet dus eerst weer €350.000 betalen en **opnieuw 15 minuten schaduwen** om je nieuwe afdeling te vinden.

**Bij de 10e Liquidatie (10/10 betekent Code Blauw en het Reanimatie-Serum)**
- Op de Liquidatiepagina zie je jouw **Reanimatie-Dossier teller (0 / 10)**. Elke keer dat je wordt geliquideerd, telt deze 1 omhoog.
- Word je voor de **10e keer geliquideerd (10 / 10)**? Dan stopt je hart en beland je in **Code Blauw (Hartstilstand)**!
- Om weer tot leven te komen met **100% HP**, **30 minuten schild** en je teller weer helemaal te resetten naar **0 / 10**, moet je in de Kliniek Shop of op de Liquidatiepagina het **Forensisch Reanimatie-Serum (Epinefrine-Ampul)** kopen voor **€1.500.000** (of 75 Sterren).
- *Slimme tip* Je kunt het Forensisch Reanimatie-Serum ook al kopen als je bijvoorbeeld op 7/10 of 8/10 staat, zodat je teller weer veilig op 0/10 springt voordat je in Code Blauw belandt!

---

### 🛡️ Hoe Werken de Nieuwe Backfire-Vesten en Munitiekisten?
Onder tabblad **3. Pantser en Munitie Arsenaal** op de Liquidatiepagina kun je jezelf zwaar bewapenen met zowel **in-game Geld (Contant en Bank gecombineerd)** als **Sterren**.

**Wat doet een Backfire-Vest?**
Een vest verhoogt je **Verdediging** flink én geeft een **Backfire-kans (Terugslag)**. Als een aanvaller op je schiet met te weinig patronen of te weinig kracht om door jouw vest heen te komen, **kaatst jouw vest de kogels terug op de schutter**!
- Bij een Backfire blijft het doelwit helemaal ongedeerd en krijgt **+25 Respect**.
- De schutter raakt zélf zwaargewond (**20 HP**), krijgt zelf **+1 op zijn 10/10 liquidatieteller** en wordt zelf per brancard afgevoerd naar een andere ruimte!

**Alle 4 de Backfire-Vesten op een rij**
- **1. Licht Kevlar Ondervest (1 Uur actief)** kost **€500.000** (of 35 ⭐) en geeft **+180 Verdediging** en **30% Backfire-kans**
- **2. Zwaar Tactisch Backfire-Vest (3 Uur actief - Populair!)** kost **€1.250.000** (of 65 ⭐) en geeft **+350 Verdediging** en **55% Backfire-kans**
- **3. Syndicaat Keramisch Pantser (6 Uur actief)** kost **€2.400.000** (of 110 ⭐) en geeft **+480 Verdediging** en **65% Backfire-kans**
- **4. Directie Titanium Exosuit (12 Uur actief)** kost **€4.500.000** (of 150 ⭐) en geeft **+650 Verdediging** en **75% Backfire-kans**

**Alle 4 de Munitiekisten op een rij**
- **1. Smokkeldoos (+50 Patronen)** kost **€250.000** (of 50 ⭐)
- **2. Paviljoen Munitiekist (+250 Patronen en +5% Koudbloedigheid)** kost **€1.100.000** (of 180 ⭐)
- **3. Syndicaat Wapenkist (+1.000 Patronen en +10% Koudbloedigheid)** kost **€3.850.000** (of 550 ⭐)
- **4. Kartel Arsenaalcontainer (+2.500 Patronen en +15% Koudbloedigheid)** kost **€8.500.000** (of 1.100 ⭐)

Veel succes met jagen, schaduwen en overleven in de kliniek!';

        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $authorId,
                'title' => $title,
                'emoji' => '🎯',
                'image_url' => null,
                'content' => $content,
                'is_pinned' => true,
                'comments_enabled' => true,
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        $this->info("Nieuw Liquidatie 2.0 uitlegartikel succesvol geplaatst! ID {$article->id}, Slug {$article->slug}");

        // 3. Clean em-dashes, semicolons and colons from all existing news articles
        foreach (NewsArticle::all() as $existing) {
            $cleanTitle = str_replace([' — ', ' – ', '—', '–', ';', ':'], [' - ', ' - ', '-', '-', '', ''], (string) $existing->title);
            $cleanContent = str_replace([' — ', ' – ', '—', '–', ';'], [' - ', ' - ', '-', '-', ','], (string) $existing->content);
            $cleanContent = (string) preg_replace('/(?<!https|http):/', '', $cleanContent);

            if ($cleanTitle !== $existing->title || $cleanContent !== $existing->content) {
                $existing->update([
                    'title' => $cleanTitle,
                    'content' => $cleanContent,
                ]);
            }
        }

        return self::SUCCESS;
    }
}
