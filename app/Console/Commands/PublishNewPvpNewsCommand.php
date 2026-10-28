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
#[Description('Publiceert het uitgebreide newbfriendly PvP nieuwsbericht over Liquidatie, Fair Play en Bloedwraak')]
class PublishNewPvpNewsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $authorCharacter = Character::where('name', 'Daniel')
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
        $title = 'Grote PvP Update: Eerlijke Liquidatie, Fair Play & Bloedwraak';
        $slug = Str::slug($title);

        $content = 'Beste patiënten, bendeleiders en nieuwkomers in de TBS Kliniek!

Vandaag lanceren we een van de belangrijkste gameplay updates van het seizoen: een complete herziening en balancering van het **Liquidatie (PvP) Wapenarsenaal**!

In het verleden kon het voorkomen dat een speler herhaaldelijk door dezelfde sterke rivaal werd neergeschoten, in één klap 100% van al het contante geld verloor en direct na het ontwaken opnieuw werd neergeknald. Dat zorgde voor frustratie en jaagt spelers weg. 

Met dit gloednieuwe systeem blijft de spanning, rivaliteit en adrenaline volop aanwezig, maar krijgt iedereen een eerlijke kans om te groeien, terug te vechten en tactisch te spelen!

---

### 🛡️ 1. Eerlijk Levelbereik (Maximaal 15 Levels Verschil)
Een speler van level 60 kan niet langer zomaar een pas gestarte speler van level 8 executeren.
- Je kunt uitsluitend schieten op doelwitten die maximaal **15 levels boven of onder** jouw eigen niveau zitten.
- Zo vecht je altijd tegen tegenstanders van vergelijkbaar niveau en blijft elke overwinning echt verdiend!

---

### ⏱️ 2. Persoonlijke Doelwit Cooldown (3 Uur Wachttijd)
Geen pesterijen of doelloos farmen op dezelfde persoon:
- Als jij speler B beschiet, mag jij speler B de komende **3 uur lang** niet opnieuw viseren.
- Na een aanval moet je dus andere doelwitten zoeken, waardoor iedereen rust krijgt.

---

### 🕊️ 3. Automatische Herstelbescherming na Coma (30 Minuten Schild)
Als je bent neergeschoten en ontwaakt uit de Intensive Care:
- Ontvang je direct een automatisch **Herstelschild van 30 minuten**.
- Gedurende deze tijd kan niemand jou liquideren.
- Dit geeft je rustig de tijd om contant geld veilig naar de bank te brengen, je HP te herstellen in de ziekenboeg en je inventaris op orde te brengen.
- *Let op: Als je tijdens deze hersteltijd zelf besluit om iemand aan te vallen, vervalt je bescherming uiteraard meteen!*

---

### 🚫 4. Daglimiet op Slachtofferschap (Maximaal 3x per 24 Uur)
Om actieve spelers en bendeleden te beschermen tegen massale gerichte heksenjachten:
- Een speler kan maximaal **3 keer per 24 uur** fatale schade oplopen door liquidaties.
- Zodra dit aantal is bereikt, is het doelwit voor de rest van de dag volledig beschermd tegen kogels.

---

### 💰 5. Buit Verlaagd van 100% naar 35% Contant Geld
Het plunderen van slachtoffers is eerlijker afgesteld:
- Bij een dodelijke voltreffer verliest het slachtoffer **35% van het losse contante geld op zak** (in plaats van alles).
- Dit levert de schutter nog steeds een gigantische beloning op, maar voorkomt dat een slachtoffer in één klap weken aan spaargeld kwijt is.
- **Gouden Tip voor Beginners:** Geld op je bankrekening kan NOOIT worden geplunderd! Zet overtollig geld dus altijd direct op de bank.

---

### 🩸 6. Bloedwraak (Blood Feuds): Samen Terugslaan met je Bende!
Neergeschoten worden is niet langer het einde van het verhaal, maar het begin van een echte bendeoorlog!
- Als jij of een lid van jouw bende wordt neergeschoten, start er automatisch een **24-uurs Bloedwraak Vete**.
- **Levelgrenzen vervallen:** Zowel jij als al jouw bendeleden mogen de dader direct liquideren, ongeacht levelverschil!
- **Cooldowns vervallen:** De 3-uurs wachttijd geldt niet tegen de dader.
- **Enorme Wraakbeloningen:** De speler die de dader terugslaat verdient **+50 extra Respect**, een contante premie van **€ 10.000**, én **+150 Bende Respect** voor jullie gezamenlijke gang!

---

### 🦺 7. Kogelwerend Vest & Het Gevaar van Backfire
Wil je jezelf optimaal beschermen?
- In het Wapenarsenaal schaf je voor 150 Gouden Sterren een **Kogelwerend Vest** aan dat 12 uur actief blijft.
- **Backfire Risico:** Als een rivaal kogels op jou afvuurt terwijl jij een vest draagt, kunnen de kogels afketsen op je pantser. De schutter raakt dan zélf zwaargewond en belandt zelf 1 uur in Coma op de Intensive Care!

---

### 💡 Handige Tips voor Beginnende Patiënten
1. **Zet je geld op de Bank:** Houd nooit miljoenen los op zak. De bank is 100% veilig en geeft dagelijks rente.
2. **Train je Verdediging:** Onder Krachttraining zorgt bankdrukken voor hogere verdediging, waardoor kogels minder snel fataal zijn.
3. **Sluit je aan bij een Bende:** Samen sta je sterker. Als iemand jou aanvalt, kunnen jouw bendeleden direct Bloedwraak nemen!
4. **Gebruik de Sterren Shop:** Heb je snel kogels nodig of wil je direct ontwaken uit coma? In de Sterren Shop regel je dit direct.

Heb je vragen of opmerkingen over het nieuwe systeem? Laat hieronder een reactie achter of open een ticket bij het kliniekpersoneel!';

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

        $this->info("PvP Nieuwsbericht succesvol geplaatst! ID: {$article->id}, Slug: {$article->slug}");

        return self::SUCCESS;
    }
}
