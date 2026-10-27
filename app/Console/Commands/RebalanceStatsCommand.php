<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Gang;
use App\Models\Item;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tbs:rebalance-stats')]
#[Description('Herstelt onevenredig hoge gevechtsstatistieken met behoud van spelersgeld en publiceert het balans nieuwsartikel')]
class RebalanceStatsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Starten met spelbalans herstel...');

        // 1. Zoek spelers met extreme stats (via shop stacking)
        $inflatedCharacters = Character::where('strength', '>', 600)
            ->orWhere('defense', '>', 600)
            ->orWhereIn('id', [42, 33, 82, 84, 70, 15])
            ->get();

        $this->info("Gevonden spelers voor herstel: {$inflatedCharacters->count()}");

        foreach ($inflatedCharacters as $char) {
            $oldStr = $char->strength;
            $oldDef = $char->defense;
            $oldSta = $char->stamina;

            $maxStat = 50 + ($char->level * 15);
            $fairStat = min($maxStat, max(50, 40 + ($char->level * 8)));

            $char->strength = $fairStat;
            $char->defense = $fairStat;
            $char->stamina = $fairStat;
            // GELD WORDT NIET AANGERAAKT - COMPENSATIE
            $char->save();

            $this->line("Speler {$char->name} (Lvl {$char->level}): Kracht {$oldStr}->{$fairStat}, Verdediging {$oldDef}->{$fairStat}, Conditie {$oldSta}->{$fairStat} (Geld behouden: €".number_format($char->cash + $char->bank, 0, ',', '.').')');

            // Ruim overtollige gestackte uitrusting in inventaris op
            $equipmentItemIds = Item::where('category', 'equipment')->pluck('id')->toArray();
            CharacterItem::where('character_id', $char->id)
                ->whereIn('item_id', $equipmentItemIds)
                ->where('quantity', '>', 1)
                ->update(['quantity' => 1]);
        }

        // 2. Corrigeer geëxploiteerde bling shops bij bendes
        $gangsToCap = Gang::where('bling_shops', '>', 15)->get();
        foreach ($gangsToCap as $g) {
            $oldShops = $g->bling_shops;
            $g->bling_shops = 15;
            $g->save();
            $this->info("Gang {$g->name} bling shops aangepast van {$oldShops} naar 15.");
        }

        // 3. Publiceer het officiële balans nieuwsbericht
        $danielCharacter = Character::where('name', 'Daniel')
            ->orWhere('username', 'daniel')
            ->orWhere('name', 'daniel')
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

        $title = 'Grote Update Eerlijke Spelbalans en Vaardigheden! 🛡️💪';
        $slug = 'grote-update-eerlijke-spelbalans';

        $content = "Beste patiënten en bewoners van de kliniek,\n\n"
            ."Vandaag voeren we een grote update door voor de spelbalans. De afgelopen periode kon je in de winkel onbeperkt uitrusting kopen waardoor gevechtsstatistieken zoals Kracht en Verdediging oneindig hoog opliepen. Hierdoor was het voor nieuwkomers en actieve spelers onmogelijk geworden om eerlijke gevechten aan te gaan.\n\n"
            ."Dat is vanaf nu verleden tijd! Hieronder leggen we helder en stap voor stap uit hoe het nieuwe systeem werkt.\n\n"
            ."## Hoe verdien je nu Kracht en Verdediging?\n\n"
            ."Vanaf vandaag word je sterker door acties uit te voeren in de kliniek. Niet door miljoenen euro's in de winkel te dumpen, maar door echte inzet!\n\n"
            ."1. Vechten op de luchtplaats\n"
            ."Wanneer je een knokpartij op de luchtplaats wint, heb je direct kans op permanente Kracht. Sla je als verdediger een beroving succesvol af, dan stijgt jouw Verdediging.\n\n"
            ."2. Armpjedrukken in de ontspanningsruimte\n"
            ."Laat je spierballen rollen aan de armworstel tafel. Zowel tegen NPC tegenstanders als tegen medespelers verdien je nu Kracht bij winst.\n\n"
            ."3. Werkplaats Dagbesteding\n"
            ."Zwaar werk in de werkplaats bij houtbewerking bouwt spieren op en levert Kracht op.\n\n"
            ."4. Barry Biceps in de gym\n"
            ."Je kunt uiteraard ook nog altijd gericht trainen bij Barry Biceps en Mo de Meesterstrateeg.\n\n"
            ."## Begrenzing per level\n\n"
            ."Om het spel eerlijk en spannend te houden voor iedereen, heeft elk level nu een natuurlijk stat plafond. Hoe hoger je level wordt door ervaring op te doen, hoe sterker je maximaal kunt worden. Zo blijft een gevecht altijd tactisch en heeft iedereen een eerlijke kans.\n\n"
            ."## Uitrusting in je rugzak\n\n"
            ."Wapens en bescherming zoals de Boksbeugel en Stalen Neus Kisten koop je eenmalig in de winkel. Zolang ze in je rugzak zitten, geven ze een krachtige passieve gevechtsbonus tijdens al je knokpartijen.\n\n"
            ."## Herstel van statistieken en compensatie\n\n"
            ."Voor de spelers die eerder tienduizenden items hebben aangeschaft, zijn de gevechtsstatistieken teruggezet naar een passend hoog niveau behorend bij hun level.\n\n"
            ."Als compensatie voor de gemaakte kosten hebben alle betrokken spelers hun volledige geldbedrag en banktegoeden mogen behouden. De kliniek directie gunt iedereen een rijke bankrekening, zolang de gevechten op de vloer maar eerlijk en sportief verlopen!\n\n"
            ."## Tips voor beginners\n\n"
            ."Ben je net nieuw in TBS Kliniek?\n"
            ."Begin rustig met dagbesteding om geld en ervaring te verzamelen. Koop een boksbeugel in de winkel voor extra slagkracht en daag spelers uit van jouw eigen niveau op de luchtplaats. Zo klim je stap voor stap op tot de absolute top van de kliniek!\n\n"
            ."Veel succes en strijdvaardigheid toegewenst,\n"
            .'De Kliniek Directie';

        NewsArticle::updateOrCreate(
            ['slug' => $slug],
            [
                'character_id' => $authorId,
                'title' => $title,
                'emoji' => '🛡️',
                'content' => $content,
                'is_pinned' => true,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => now(),
            ]
        );

        $this->info('Nieuwsartikel succesvol gepubliceerd!');
        $this->info('Spelbalans herstel succesvol afgerond.');

        return Command::SUCCESS;
    }
}
