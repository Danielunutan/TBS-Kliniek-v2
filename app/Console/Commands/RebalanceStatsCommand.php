<?php

namespace App\Console\Commands;

use App\Models\ArmWrestleMatch;
use App\Models\Character;
use App\Models\CharacterArmWrestleStat;
use App\Models\CharacterItem;
use App\Models\Gang;
use App\Models\Item;
use App\Models\NewsArticle;
use App\Models\TrainingLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('tbs:rebalance-stats')]
#[Description('Herstelt onevenredig hoge gevechtsstatistieken met behoud van spelersgeld en publiceert het balans nieuwsartikel')]
class RebalanceStatsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Starten met spelbalans herstel...');

        // 1. Zoek spelers met extreme stats of actieve spelers die hersteld moeten worden
        $inflatedCharacters = Character::where('level', '>=', 5)
            ->where(function ($query) {
                $query->where('strength', '>', 500)
                    ->orWhere('defense', '>', 500)
                    ->orWhereIn('id', [42, 33, 17, 15, 84, 82, 70, 58, 86, 6]);
            })
            ->get();

        $this->info("Gevonden spelers voor herstel: {$inflatedCharacters->count()}");

        foreach ($inflatedCharacters as $char) {
            $oldStr = $char->strength;
            $oldDef = $char->defense;
            $oldSta = $char->stamina;
            $oldInt = $char->intelligence;
            $oldCha = $char->charisma;
            $oldDis = $char->discipline;

            $maxStat = $char->getMaxStatForLevel();

            // 1. Haal alle sportschool workouts per stat op
            $tLogs = TrainingLog::where('character_id', $char->id)
                ->selectRaw('stat, sum(stat_gain) as total_gain')
                ->groupBy('stat')
                ->pluck('total_gain', 'stat')
                ->toArray();

            $tStr = (int) ($tLogs['strength'] ?? 0);
            $tDef = (int) ($tLogs['defense'] ?? 0);
            $tSta = (int) ($tLogs['stamina'] ?? 0);
            $tInt = (int) ($tLogs['intelligence'] ?? 0);
            $tCha = (int) ($tLogs['charisma'] ?? 0);
            $tDis = (int) ($tLogs['discipline'] ?? 0);

            // 2. Haal alle armworstel overwinningen op
            $armStat = CharacterArmWrestleStat::where('character_id', $char->id)->first();
            $armMatchesWon = ArmWrestleMatch::where('winner_id', $char->id)->count();
            $totalArmWins = max($armStat ? $armStat->wins : 0, $armMatchesWon);

            // 3. Haal overige voltooide activiteiten op
            $fightsWon = (int) ($char->fights_won ?? 0);
            $dagb = (int) ($char->dagbesteding_shifts_completed ?? 0);
            $theftsWon = DB::table('theft_records')->where('thief_id', $char->id)->where('success', 1)->count();
            $gangAttacks = DB::table('gang_attacks')->where('character_id', $char->id)->count();

            // 4. Bereken verdiende punten per statistiek op basis van alle inspanningen
            // Kracht: sportschool + armpjedrukken (1.0 per winst) + gevechten (0.5) + bendeaanvallen
            $earnedStr = 10 + $tStr + (int) round($totalArmWins * 1.0) + (int) round($fightsWon * 0.5) + $gangAttacks;

            // Verdediging: sportschool + gevechten incasseren (0.5) + armpjedrukken (0.3) + bendeaanvallen
            $earnedDef = 10 + $tDef + (int) round($fightsWon * 0.5) + (int) round($totalArmWins * 0.3) + $gangAttacks;

            // Conditie: sportschool + zware dagbesteding (1.5 per dienst) + zakkenrollen conditie (1.0) + gevechten (0.3) + armpjedrukken (0.2)
            $earnedSta = 10 + $tSta + (int) round($dagb * 1.5) + (int) round($theftsWon * 1.0) + (int) round($fightsWon * 0.3) + (int) round($totalArmWins * 0.2);

            // Intelligentie: sportschool + zakkenrollen en tactiek (1.0 per diefstal)
            $earnedInt = 10 + $tInt + (int) round($theftsWon * 1.0);

            // Charisma: sportschool contacten en sociale vaardigheden
            $earnedCha = 10 + $tCha;

            // Discipline: sportschool + dagbesteding discipline (0.5) + dagelijkse actieve streak
            $earnedDis = 10 + $tDis + (int) round($dagb * 0.5) + (int) ($char->streak_days ?? 0);

            // 5. Natuurlijke ondergrens voor hogere levels zodat ervaren leiders niet achterblijven in mentale/sociale stats
            $combatFloor = 10 + ($char->level * 5);
            $socialFloor = 10 + ($char->level * 4);

            // 6. Toekennen met respect voor eerlijke gameplay, begrensd door het level plafond
            $char->strength = min($maxStat, max($char->strength > $maxStat ? 0 : $char->strength, max($earnedStr, $combatFloor)));
            $char->defense = min($maxStat, max($char->defense > $maxStat ? 0 : $char->defense, max($earnedDef, $combatFloor)));
            $char->stamina = min($maxStat, max($char->stamina > $maxStat ? 0 : $char->stamina, max($earnedSta, $combatFloor)));
            $char->intelligence = min($maxStat, max($char->intelligence > $maxStat ? 0 : $char->intelligence, max($earnedInt, $socialFloor)));
            $char->charisma = min($maxStat, max($char->charisma > $maxStat ? 0 : $char->charisma, max($earnedCha, $socialFloor)));
            $char->discipline = min($maxStat, max($char->discipline > $maxStat ? 0 : $char->discipline, max($earnedDis, $socialFloor)));

            // GELD WORDT NIET AANGERAAKT - COMPENSATIE BEHOUDEN
            $char->save();

            $this->line("Speler {$char->name} (Lvl {$char->level}, Cap {$maxStat}): Str {$oldStr}->{$char->strength}, Def {$oldDef}->{$char->defense}, Sta {$oldSta}->{$char->stamina}, Int {$oldInt}->{$char->intelligence}, Cha {$oldCha}->{$char->charisma}, Dis {$oldDis}->{$char->discipline}");

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
            ."Vandaag voeren we een grote update door voor de spelbalans. De afgelopen periode kon je in de winkel onbeperkt uitrusting kopen waardoor statistieken oneindig hoog konden oplopen. Hierdoor was het voor nieuwkomers en actieve spelers onmogelijk geworden om eerlijke gevechten aan te gaan.\n\n"
            ."Dat is vanaf nu verleden tijd! Hieronder leggen we helder en stap voor stap uit hoe het nieuwe systeem werkt.\n\n"
            ."## Hoe verdien je nu jouw eigenschappen en vaardigheden?\n\n"
            ."Vanaf vandaag word je sterker door echte acties uit te voeren in de kliniek. Niet door miljoenen euro's in de winkel te dumpen, maar door echte inzet over de hele linie!\n\n"
            ."1. Vechten op de luchtplaats en bendeoorlogen\n"
            ."Wanneer je een knokpartij op de luchtplaats aangaat of meedoet aan bendeaanvallen, stijgen zowel jouw Kracht als jouw Verdediging en Conditie door de adrenaline en het incasseringsvermogen.\n\n"
            ."2. Armpjedrukken in de ontspanningsruimte\n"
            ."Laat je spierballen rollen aan de armworstel tafel. Zowel tegen NPC tegenstanders als tegen medespelers verdien je volop Kracht, Verdediging en fysieke Conditie bij winst.\n\n"
            ."3. Werkplaats Dagbesteding\n"
            ."Zwaar werk in de werkplaats bij houtbewerking, de wasserette en de keuken bouwt fysieke Conditie en Discipline op.\n\n"
            ."4. Zakkenrollen en diefstallen\n"
            ."Het ongezien opereren en snel wegkomen traint je Conditie en tactische Intelligentie.\n\n"
            ."5. De Sportschool van Barry Biceps en Mo\n"
            ."Je kunt uiteraard ook nog altijd gericht trainen bij alle zes kliniektrainers voor Kracht, Verdediging, Conditie, Intelligentie, Charisma en Discipline.\n\n"
            ."## Begrenzing per level\n\n"
            ."Om het spel eerlijk en spannend te houden voor iedereen, heeft elk level nu een natuurlijk stat plafond. Hoe hoger je level wordt door ervaring op te doen, hoe sterker je maximaal kunt worden. Zo blijft een gevecht altijd tactisch en heeft iedereen een eerlijke kans.\n\n"
            ."## Uitrusting in je rugzak\n\n"
            ."Wapens en bescherming zoals de Boksbeugel en Stalen Neus Kisten koop je eenmalig in de winkel. Zolang ze in je rugzak zitten, geven ze een krachtige passieve gevechtsbonus tijdens al je knokpartijen.\n\n"
            ."## Volledige waardering van alle trainingen en compensatie\n\n"
            ."Voor alle spelers zijn alle statistieken opnieuw gecontroleerd en volledig verrekend met al hun armworstelpartijen, sportschooluren, straatgevechten en dagbestedingsdiensten. Niemand wordt tekortgedaan!\n\n"
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
