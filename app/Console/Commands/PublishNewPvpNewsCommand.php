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
#[Description('Verwijdert het oude PvP nieuwsbericht en publiceert het nieuwe Liquidatie 2.0 artikel')]
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
            Str::slug('Grote PvP Update: Eerlijke Liquidatie, Fair Play & Bloedwraak'),
        ];

        $deletedCount = NewsArticle::whereIn('slug', $oldSlugs)
            ->orWhere('title', 'like', '%Eerlijke Liquidatie%')
            ->orWhere('title', 'like', '%Liquidatie 2.0%')
            ->delete();

        $this->info("Oude PvP nieuwsberichten verwijderd ({$deletedCount})");

        // 2. Create & Pin the new Liquidatie 2.0 article without em-dashes, semicolons or colons
        $title = 'Grote Update Liquidatie 2.0 met 30 Dossier Gradaties en 3 Uurs Backfire Vest';
        $slug = Str::slug($title);

        $content = 'Beste patiënten en bendeleiders van de TBS Kliniek!

Op basis van jullie feedback hebben we het **Liquidatie en Inlichtingensysteem** vanaf de grond opnieuw opgebouwd tot een uitgebreid en tactisch systeem waarin **jagen door de kliniekruimtes, schaduw-inlichtingen, zware Backfire-vesten voor geld en precisie-executies** centraal staan!

Hieronder lees je precies hoe **Liquidatie 2.0** werkt.

---

### 🎯 1. De 30 TBS Dossier-Gradaties en Koudbloedigheid (%)
In plaats van dat iedereen hetzelfde aantal kogels schiet, wordt jouw status in het liquidatiecircuit bepaald door **30 Forensische Dossier-Gradaties** (gebaseerd op je Level en je voltooide afrekeningen) en je **Koudbloedigheid (0% tot 100%)**.
- **Vanaf Dossier-Graad 1 (*Observatiepatiënt*)** kan iedereen schieten, maar heb je een zwaar salvo aan patronen nodig. Hoe hoger je Dossier-Graad klimt, hoe minder patronen je nodig hebt.
- **Genadeschot (1-Patroon Precisie-Executie)** Zodra je **Dossier-Graad 25 (*Forensisch Scherpschutter*)** of hoger bereikt en je **Koudbloedigheid op minimaal 30%** staat, ontgrendel je het dodelijke **1-Patroon Genadeschot**!
- **Zit je onder de 30% Koudbloedigheid?** Dan trilt je hand en kun je niet met 1 patroon schieten totdat je je Koudbloedigheid weer hebt opgetraind op de **Ondergrondse Schietbaan** of via **Vechtoefening**.

---

### 🕵️ 2. Zelfde Kliniek-Afdeling Vereist en 15 Minuten Schaduw-Inlichtingen
Je kunt niet langer blind vanuit een andere vleugel op iemand vuren.
- **Zelfde Ruimte Vereist** Om de trekker over te halen moet je in **exact dezelfde kliniek-afdeling** staan als je doelwit (bijvoorbeeld *Paviljoen 1*, *De Luchtplaats*, *Het Kookhuis* of de *Recreatiezaal*).
- **Camera en Gangpad Schaduwing (15 Minuten)** Weet je niet op welke afdeling een rivaal rondloopt? Start dan een **Schaduw-Inlichtingen Operatie (€350.000)**. Na **15 minuten** ontvang je een live rapport met de exacte kliniek-vleugel van je doelwit, zijn gedragen pantser en zijn vermogen, inclusief een **1-klik knop om direct naar die vleugel te reizen**!
- **Celblok-Lockdown (€1.750.000)** Wil je voorkomen dat je doelwit wegvlucht? Met een Celblok-Lockdown wordt de afdeling van het doelwit zodra het rapport binnen is **10 minuten lang vergrendeld**!

---

### 🛡️ 3. Nieuw 3-Uurs Backfire-Vest voor Geld en Uitgebreid Arsenaal
Jullie vroegen massaal om een **3-uurs Backfire-vest dat met in-game geld te koop is**. Het Wapenarsenaal biedt nu 4 pantserklassen die zowel met **Geld (Contant en Bank)** als **Sterren** verkrijgbaar zijn.
- **Licht Kevlar Ondervest (1 Uur)** voor €500.000 (of 35 ⭐) geeft +180 Def en **30% Backfire-kans**
- **Zwaar Tactisch Backfire-Vest (3 Uur)** voor **€1.250.000** (of 65 ⭐) geeft +350 Def en **55% Backfire-kans**
- **Syndicaat Keramisch Pantser (6 Uur)** voor €2.400.000 (of 110 ⭐) geeft +480 Def en **65% Backfire-kans**
- **Directie Titanium Exosuit (12 Uur)** voor €4.500.000 (of 150 ⭐) geeft +650 Def en **75% Backfire-kans**

---

### 💰 4. Verhoogde Buit van 45% Contant Geld en 7% Bankrekening!
Liquideren levert voortaan serieuze buit op.
- Bij elke dodelijke voltreffer roof je direct **45% van het contante geld** en **7% van de bankrekening** van het slachtoffer!
- Daarnaast verdien je **+60 Respect**, **+400 XP** en strijk je automatisch eventuele openstaande **Kopgeld-premies** en **Bloedwraak-bonussen** op.

---

### 💉 5. Onbeperkt Jagen, Brancard-Evacuatie en Code Blauw bij 10x Geliquideerd
We hebben de frustrerende 1-uurs coma-blokkade bij losse treffers en de daglimiet van 3 kills verwijderd.
- **Liquidatie 1 t/m 9 (Direct Healen en Brancard-Evacuatie)** Word je neergeschoten? Dan zit je **niet** vast in een coma! Je overleeft met **20 HP**, kunt **direct eten en healen**, en wordt per brancard geëvacueerd naar een **willekeurige andere kliniek-afdeling** met **15 minuten Medisch Infuus-Schild**. Wil een jager je opnieuw pakken, dan moet hij eerst opnieuw 15 minuten schaduwen om je nieuwe vleugel te vinden!
- **10x Geliquideerd betekent Code Blauw (Forensisch Reanimatie-Serum)** Ben je **10 keer geliquideerd (10/10)**? Dan treedt **Code Blauw (Hartstilstand)** in! Om weer tot leven te komen met 100% HP en je teller terug te zetten naar **0/10**, moet je in de kliniek het **Forensisch Reanimatie-Serum (Epinefrine-Ampul)** aanschaffen voor **€1.500.000** (of 75 Sterren).

Ga nu naar **Liquidatie** in het menu, bekijk je Dossier-Graad en beheers de kliniek!';

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

        $this->info("Nieuw Liquidatie 2.0 artikel succesvol geplaatst! ID {$article->id}, Slug {$article->slug}");

        return self::SUCCESS;
    }
}
