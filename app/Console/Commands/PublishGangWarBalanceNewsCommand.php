<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\ChatBotService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('tbs:publish-gang-war-update')]
#[Description('Publiceert het nieuwsartikel over de bende oorlog balans update en plaatst een melding in de live chat')]
class PublishGangWarBalanceNewsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ChatBotService $chatBotService): int
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

        $title = '🚨 Bende Oorlog Balans Update: Eerlijke Strijd, Transparante Verdediging & Enforcer Cap!';
        $slug = Str::slug('bende-oorlog-balans-update-eerlijke-strijd-transparante-verdediging-enforcer-cap');

        // Remove previous edition if exists
        NewsArticle::where('slug', $slug)
            ->orWhere('title', 'like', '%Bende Oorlog Balans Update%')
            ->delete();

        $content = <<<'MARKDOWN'
Beste bendeleiders, officieren en strijders van de TBS Kliniek!

Naar aanleiding van waardevolle feedback uit de community hebben we het **Bende Oorlog Systeem** grondig geanalyseerd en per direct geherbalanceerd. Vanaf heden zijn gevechten 100% transparant en is de verhouding tussen aanval en verdediging weer helemaal eerlijk en gebalanceerd!

Hieronder vind je alle verbeteringen die zojuist live zijn gegaan:

---

### 🛡️ 1. Transparante Verdediging & Enforcers Zichtbaar
Voorheen werd op het openbare bende-profiel en in de bende-lijst alleen de basisbescherming getoond. Hulpkrachten (zoals Enforcer-bewakers in panden) waren verborgen, waardoor verdedigingen onverwacht veel hoger uitvielen dan zichtbaar was.

* **Wat is er veranderd?**
  * Zowel op het bende-profiel als in het **Bende Oorlog** tabblad zie je nu direct de **Totale Verdediging** inclusief het exacte percentage van je Enforcers (bijv. `Totale Verdediging: 1.488.000 (+40% Enforcers)`).
  * Ook de bevestigingspopup voor een overval toont nu exact de werkelijke verdedigingsrol inclusief alle Enforcer-bonussen. Geen verborgen verrassingen meer!

---

### ⚖️ 2. Maximum Cap op Enforcer-Verdediging (Max +100%)
In bende-panden kunnen specialistische Enforcers worden geplaatst om je bende te beveiligen. Voorheen zat hier geen maximum op, waardoor bendes met veel panden hun verdediging konden opstapelen tot honderden procenten en letterlijk onkwetsbaar werden.

* **De nieuwe regel:**
  * De totale verdedigingsbonus door Enforcers is nu **gelimiteerd tot maximaal +100%** (een maximale verdubbeling van de basisverdediging).
  * Enforcers blijven hiermee een gigantisch sterke investering voor elke bende, maar het zorgt ervoor dat een bende met hoge Power alsnog door de linies heen kan breken!

---

### ⚡ 3. Winkel Balans: +5.000 Power per Aankoop
Voorheen leverde een aankoop van Bescherming in de Bende Winkel +5.000 punten op, terwijl Power slechts +2.500 opleverde voor dezelfde €15.000 uit de gangpot.

* **De nieuwe regel:**
  * Power aanschaffen in de Bende Winkel levert nu **+5.000 Power** op per aankoop (was 2.500).
  * Hierdoor groeit de aanvalskracht van bendes evenredig mee met de verdedigingsmogelijkheden en loont het om samen met je bendeleden te sparen voor oorlog!

---

### 🩸 4. Eerlijker Verliespercentage (2%–5% ipv 5%–12%)
Als een aanval werd afgeslagen, verloor de aanvallende bende tussen de 5% en 12% van hun totale Power. Bij bendes met miljoenen power leidde één mislukte aanval daardoor tot een te harde afstraffing.

* **De nieuwe regel:**
  * De schade aan de aanvallende bende bij een afgeslagen aanval is verlaagd naar **2% tot 5% Power**.
  * Zo blijft er een reëel risico verbonden aan overvallen, maar raakt een bende niet direct al haar zuurverdiende power kwijt bij een nipte nederlaag.

---

### 🎯 5. Korte Herinnering: Hoe werkt het gevecht?
* **Aanvaller:** Vecht uitsluitend met zijn **Power (Aanvalskracht)**. Eigen verdediging blijft thuis.
* **Verdediger:** Vecht uitsluitend met zijn **Totale Verdediging (Basis + Enforcers)**. Vijandige power verdedigt niet mee.
* **Gevechtsrol:** Beide partijen krijgen een rol van **85% tot 115% (±15%)**. De hoogste score wint!

Heel veel succes op het slagveld! Spaar met je bende, bewaak je panden en heers over FPC De Balk! 👑
MARKDOWN;

        $article = NewsArticle::create([
            'character_id' => $authorId,
            'title' => $title,
            'slug' => $slug,
            'emoji' => '⚔️',
            'image_url' => 'assets/illustrations/banner_gangs.jpg',
            'content' => $content,
            'is_pinned' => true,
            'comments_enabled' => true,
            'is_published' => true,
            'views_count' => 0,
            'published_at' => now(),
        ]);

        $this->info("Nieuwsartikel succesvol gepubliceerd (ID: {$article->id}, Slug: {$article->slug})");

        // Broadcast to live chat
        $chatMessage = '🚨 UPDATE: Bende Oorlogen zijn geherbalanceerd! Enforcer-verdediging is nu transparant zichtbaar, gecapt op max +100%, Power in de winkel verhoogd naar +5.000 per aankoop en verliesstraffen verlaagd. Lees het volledige nieuwsbericht op de nieuwspagina! ⚔️';
        $chatBotService->broadcast($chatMessage, 'global');

        $this->info('Chatbericht succesvol geplaatst in de algemene live chat!');

        return self::SUCCESS;
    }
}
