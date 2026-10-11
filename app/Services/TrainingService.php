<?php

namespace App\Services;

use App\Models\Character;
use App\Models\TrainingLog;
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
     * Train an attribute.
     *
     * @return array{success: bool, message: string, stat_gain: int, new_value: int}
     */
    public function train(Character $character, string $stat): array
    {
        if (! array_key_exists($stat, self::STATS)) {
            throw new InvalidArgumentException("Ongeldige eigenschap: {$stat}");
        }

        if ($character->isImprisoned()) {
            return [
                'success' => false,
                'message' => 'Je zit in de isoleercel en kunt nu niet trainen!',
                'stat_gain' => 0,
                'new_value' => $character->{$stat},
            ];
        }

        $currentValue = (int) $character->{$stat};
        $energyCost = 10;
        $cashCost = (int) max(20, round(15 + ($currentValue * 3.5)));

        if ($character->cash < $cashCost) {
            return [
                'success' => false,
                'message' => "Je hebt €{$cashCost} nodig om {$stat} te trainen, maar je hebt slechts €{$character->cash} contant.",
                'stat_gain' => 0,
                'new_value' => $currentValue,
            ];
        }

        if (! $this->energyService->consume($character, $energyCost)) {
            return [
                'success' => false,
                'message' => "Niet genoeg energie! Trainen kost {$energyCost} energie.",
                'stat_gain' => 0,
                'new_value' => $currentValue,
            ];
        }

        $this->economyService->deductCash($character, $cashCost);

        // Stat gain
        $gain = 1;
        $character->{$stat} = $currentValue + $gain;
        $character->save();

        // Training also rewards a little XP
        $this->levelService->addXp($character, 12);

        TrainingLog::create([
            'character_id' => $character->id,
            'stat' => $stat,
            'stat_gain' => $gain,
            'energy_cost' => $energyCost,
            'cash_cost' => $cashCost,
            'trainer' => self::STATS[$stat]['trainer'],
        ]);

        $statName = self::STATS[$stat]['name'];
        $trainer = self::STATS[$stat]['trainer'];

        return [
            'success' => true,
            'message' => "Top workout! Samen met {$trainer} getraind. Je {$statName} is gestegen naar {$character->{$stat}}! (+12 XP)",
            'stat_gain' => $gain,
            'new_value' => (int) $character->{$stat},
        ];
    }
}
