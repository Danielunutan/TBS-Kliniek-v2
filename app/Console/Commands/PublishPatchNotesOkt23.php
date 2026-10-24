<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('tbs:publish-patch-notes-okt23')]
#[Description('Publiceert de officiële patch notes van 23 oktober 2026 met alle verbeteringen en bugfixes')]
class PublishPatchNotesOkt23 extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Zoek Daniël zijn character
        $danielCharacter = Character::where('id', 6)
            ->orWhere('name', 'like', '%Daniël%')
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

        // Unpin vorige pinned artikelen zodat dit artikel bovenaan staat
        NewsArticle::where('is_pinned', true)->update(['is_pinned' => false]);

        $title = '🛠️ Patch Notes 23 Oktober – Bugfixes, Live Chat Fix & Kliniek Verbeteringen';
        $slug = Str::slug('patch-notes-23-oktober-bugfixes-live-chat-fix-kliniek-verbeteringen');

        $content = 'Beste patiënten en bendeleden,

Vandaag rollen we een grote reeks gerichte bugfixes, handige nieuwe functies en kwaliteitsverbeteringen uit naar aanleiding van community feedback via tickets en suggesties. Lees hieronder wat er allemaal nieuw en gefixt is!

[credit=carootje]Met dank aan carootje voor de scherpe melding via Support Ticket over de stilstaande timers tijdens het wachten. Alle cooldowns tellen nu live in realtime af![/credit]

[credit=kechmina]Dank aan kechmina voor de melding dat de Live Chat-knop op mobiel de onderste navigatiebalk overlapte. De knop zweeft nu netjes erboven.[/credit]

---

## ⏱️ 1. Realtime Aflopende Cooldowns & Auto-Ontgrendeling

Voorheen bleven timers bij acties stilvallen op de seconden die tijdens het laden van de pagina waren berekend. Als speler moest je wachten en vervolgens handmatig op F5 drukken om te zien of je weer mocht spelen.

**Wat is er aangepast:**
- **Live aftellende klok:** Bij alle misdaden en klussen (**Zakkenrollen**, **Afpersen**, **Hosselen**, **Kluisjes Kraken**, **Pakketjes Onderscheppen**, **Smokkelproducties** en **Bende Oorlog**) telt de timer nu elke seconde live af in je browser.
- **Automatische unlock (geen F5 meer nodig):** Zodra de timer 00:00 bereikt, verdwijnt de cooldown banner direct en worden de actieknoppen automatisch ontgrendeld en aanklikbaar!
- **Instant synchronisatie met Ads:** Kijk je een sponsor video om de cooldown te resetten? Dan ontgrendelt het scherm direct live.

---

## 📲 2. TBS Kliniek als Native Web App (PWA) voor Mobiel & Desktop

Je kunt TBS Kliniek nu rechtstreeks installeren als een officiële stand-alone app op je telefoon, tablet of computer!

**Wat biedt de Native Web App:**
- **Echte App Ervaring:** Geen storende adresbalken of browsertabbladen meer, maar een fullscreen game-omgeving die start vanaf je startscherm of taakbalk.
- **Snellere laadtijden & Offline caching:** Belangrijke stijlen, geluiden en iconen worden lokaal gecacht waardoor de game vliegensvlug opent.
- **Installatie Advies & Gids:** Via het menu of de subtiele banner kun je met één klik de app toevoegen op Android/Chrome, of via de \'Zet op beginscherm\' stap op iPhone/Safari.

---

## 📸 3. Direct Eigen Profielfoto Uploaden via Profiel

Het personaliseren van je account is nog makkelijker gemaakt:

- Op je eigen **Profielpagina (/profiel)** kun je nu direct vanaf je smartphone of pc een foto kiezen en uploaden.
- Inclusief **live fotopreview**: je ziet direct hoe je gekozen foto in je ronde patiëntenavatar past voordat je op opslaan klikt.
- Ondersteunt alle gangbare formaten (JPG, PNG, WebP) met automatische veilige verwerking.

---

## 👑 4. Dashboard Bende Kaart Toont Officieel Bende Embleem

- Op het persoonlijke Dashboard toont de Bende Kaart nu direct het officiële avatar-embleem of de geüploade bende-afbeelding van jouw bende.
- Geen statische placeholder meer, maar direct herkenbare trots van jouw factie!

---

## ⚡ 5. Browser Vorige/Terug Glitch Opgelost (Alt + Links)

- Spelers meldden dat bij het terugklikken in de browser (of met de sneltoets `Alt + Links`) de styling of layout van de pagina soms versprong of glitchte.
- De asset bundeling en Back-Forward Cache (BFCache) zijn geoptimaliseerd: de stylesheet en elementen blijven nu 100% strak in vorm bij elke paginabeweging.
- Het JavaScript-pakket is bovendien met meer dan 60% verkleind, wat zorgt voor merkbaar snellere navigatie in de kliniek.

---

## 💬 6. Live Chat Launcher & Mobiele Navigatie Fix

- De zwevende Live Chat-knop rechtsonder zweeft nu altijd perfect boven de vaste onderste navigatiebalk op smartphones en tablets.
- Geen overlap meer met Luchtplaats, Bendes of Profiel; alle knoppen zijn vlekkeloos aan te tikken.

---

## 🛡️ 7. Flash Notificaties & Ticket Berichten

- Flash meldingen (succes, fout, buit-overzichten) na misdaden en trainingen verschijnen nu altijd direct en betrouwbaar, zelfs tijdens actieve achtergrond-polling.
- Beantwoorde tickets tonen duidelijke status-badges en nette paragraafopmaak.

---

We blijven de TBS Kliniek elke week actiever, vlotter en leuker maken. Veel plezier met spelen en laat gerust van je horen via Support Tickets of in de Live Chat!

Met vriendelijke groet,  
Het TBS Kliniek Team & Daniël';

        $article = NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $authorId,
                'title' => $title,
                'emoji' => '🛠️',
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
