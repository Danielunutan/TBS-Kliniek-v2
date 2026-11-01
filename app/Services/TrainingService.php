<?php

namespace App\Services;

use App\Models\Character;
use App\Models\TrainingLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrainingService
{
    /**
     * Trainers metadata per stat.
     *
     * @var array<string, array{name: string, trainer: string, desc: string}>
     */
    public const STATS = [
        'strength' => [
            'name' => 'Kracht',
            'trainer' => 'Barry Biceps',
            'desc' => 'Drukken en squatten met zware gietijzeren gewichten in de sportzaal.',
        ],
        'defense' => [
            'name' => 'Verdediging',
            'trainer' => 'Mo de Meesterstrateeg',
            'desc' => 'Ontwijken en incasseren achter de betonnen pingpongtafel.',
        ],
        'stamina' => [
            'name' => 'Conditie',
            'trainer' => 'Ome Willem de Wasbaas',
            'desc' => 'Trappenlopen en sprintjes trekken rond het omheinde binnenplein.',
        ],
        'intelligence' => [
            'name' => 'Intelligentie',
            'trainer' => 'Dokter Debbie Diagnose',
            'desc' => 'Complexe puzzels oplossen en het kliniekreglement uit je hoofd leren.',
        ],
        'charisma' => [
            'name' => 'Charisma',
            'trainer' => 'Ricardo Ritselaar',
            'desc' => 'Onderhandelen over frikandellen en contacten leggen bij de koffiehoek.',
        ],
        'discipline' => [
            'name' => 'Discipline',
            'trainer' => 'Directeur Gerda Graaigraag',
            'desc' => 'Administratieve procedures en stipt op tijd melden in de gang.',
        ],
    ];

    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Train an attribute or focus on level progression.
     *
     * @return array{success: bool, message: string, stat_gain: int, new_value: int, xp_gain?: int, leveled_up?: bool}
     */
    public function train(Character $character, string $stat, string $mode = 'stat'): array
    {
        if (! array_key_exists($stat, self::STATS)) {
            throw new InvalidArgumentException("Ongeldige eigenschap: {$stat}");
        }

        return DB::transaction(function () use ($character, $stat, $mode): array {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked) {
                return [
                    'success' => false,
                    'message' => 'Karakter niet gevonden.',
                    'stat_gain' => 0,
                    'new_value' => (int) $character->{$stat},
                ];
            }

            if ($locked->isImprisoned()) {
                return [
                    'success' => false,
                    'message' => 'Je zit in de isoleercel en kunt nu niet trainen!',
                    'stat_gain' => 0,
                    'new_value' => (int) $locked->{$stat},
                ];
            }

            if ($locked->isComatose()) {
                return [
                    'success' => false,
                    'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
                    'stat_gain' => 0,
                    'new_value' => (int) $locked->{$stat},
                ];
            }

            $locked->syncStatsWithLevelCap();

            $currentValue = (int) $locked->{$stat};
            $maxStat = $locked->getMaxStatForLevel();
            $statName = self::STATS[$stat]['name'];
            $trainer = self::STATS[$stat]['trainer'];

            if ($mode === 'stat' && $currentValue >= $maxStat) {
                return [
                    'success' => false,
                    'message' => "Je hebt het maximum voor {$statName} bereikt voor jouw huidige level ({$maxStat})! Kies 'Level Focus (+50 XP)' of stijg eerst in level om verder te trainen.",
                    'stat_gain' => 0,
                    'new_value' => $currentValue,
                ];
            }

            $energyCost = 10;
            $cashCost = (int) max(20, round(15 + ($currentValue * 3.5)));

            if ($locked->cash < $cashCost) {
                return [
                    'success' => false,
                    'message' => "Je hebt €{$cashCost} nodig om {$statName} te trainen, maar je hebt slechts €{$locked->cash} contant.",
                    'stat_gain' => 0,
                    'new_value' => $currentValue,
                ];
            }

            if (! $this->energyService->consume($locked, $energyCost)) {
                return [
                    'success' => false,
                    'message' => "Niet genoeg energie! Trainen kost {$energyCost} energie.",
                    'stat_gain' => 0,
                    'new_value' => $currentValue,
                ];
            }

            $locked->cash -= $cashCost;
            $locked->save();

            if ($mode === 'level') {
                $gain = 0;
                $xpReward = 50;
                $levelResult = $this->levelService->addXp($locked, $xpReward);

                TrainingLog::create([
                    'character_id' => $locked->id,
                    'stat' => $stat,
                    'stat_gain' => 0,
                    'energy_cost' => $energyCost,
                    'cash_cost' => $cashCost,
                    'trainer' => $trainer,
                ]);

                $character->refresh();

                $levelUpSuffix = $levelResult['leveled_up']
                    ? " 🎉 Je bent gestegen naar Level {$locked->level} (nieuw plafond: {$locked->getMaxStatForLevel()})!"
                    : '';

                return [
                    'success' => true,
                    'message' => "Top workout (Level Focus)! Samen met {$trainer} getraind voor +{$xpReward} XP!{$levelUpSuffix}",
                    'stat_gain' => 0,
                    'xp_gain' => $xpReward,
                    'leveled_up' => $levelResult['leveled_up'],
                    'new_value' => (int) $locked->{$stat},
                ];
            }

            // Stat gain strictly clamped to the level maximum
            $gain = min(1, max(0, $maxStat - $currentValue));
            $locked->{$stat} = min($maxStat, $currentValue + $gain);
            $locked->save();

            // Training also rewards a little XP
            $xpReward = 12;
            $levelResult = $this->levelService->addXp($locked, $xpReward);

            TrainingLog::create([
                'character_id' => $locked->id,
                'stat' => $stat,
                'stat_gain' => $gain,
                'energy_cost' => $energyCost,
                'cash_cost' => $cashCost,
                'trainer' => $trainer,
            ]);

            $character->refresh();

            return [
                'success' => true,
                'message' => "Top workout! Samen met {$trainer} getraind. Je {$statName} is gestegen naar {$locked->{$stat}}/{$locked->getMaxStatForLevel()}! (+{$xpReward} XP)",
                'stat_gain' => $gain,
                'xp_gain' => $xpReward,
                'leveled_up' => $levelResult['leveled_up'],
                'new_value' => (int) $locked->{$stat},
            ];
        });
    }
}
