<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterMission;
use App\Models\Mission;

class MissionService
{
    /**
     * Interactive scenario definitions per mission id/slug.
     */
    public const SCENARIOS = [
        1 => [
            'npc_name' => 'Henkie de Frikandelkoning',
            'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
            'npc_role' => 'Kantine Baas Paviljoen 3',
            'dilemma' => 'Hé maat, luister goed. In de warmhoudkast liggen 3 frikandelbroodjes van 150 KC per stuk. Bewaker Peter wil 100 KC smeergeld om de deur niet op slot te draaien. Wat is het exacte totaalbedrag dat we nodig hebben?',
            'correct_choice' => 'B',
            'choices' => [
                'A' => '450 KC (alleen de broodjes)',
                'B' => '550 KC (3x 150 KC + 100 KC smeergeld)',
                'C' => '650 KC (inclusief extra fooi)',
            ],
            'feedback_wrong' => 'Henkie schudt zijn hoofd: "Je rekenwerk klopt voor geen meter! Peter weigerde en smeet de warmhoudkast dicht."',
        ],
        2 => [
            'npc_name' => 'Ricardo Ritselaar',
            'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
            'npc_role' => 'Rookruimte Strateeg',
            'dilemma' => 'Patiënt Barry zit te knikkebollen in de rookruimte. Zijn kluissleutel steekt half uit zijn broekzak, maar de gangcamera zwenkt elke 30 seconden. Hoe pak je dit aan?',
            'correct_choice' => 'B',
            'choices' => [
                'A' => 'Direct rennen en de sleutel met een ruk uit zijn zak trekken.',
                'B' => 'Wachten tot de camera wegdraait, rustig langslopen en vingervlug toeslaan.',
                'C' => 'Tegen Barry schreeuwen dat de zuster pillen komt uitdelen.',
            ],
            'feedback_wrong' => 'Ricardo zucht: "Je was veel te lomp! Barry schrok wakker en greep je arm vast. Mislukt!"',
        ],
        3 => [
            'npc_name' => 'Barry Biceps',
            'npc_avatar' => 'assets/characters/avatar_male_3.jpg',
            'npc_role' => 'Sportzaal Veteraan',
            'dilemma' => 'Voordat we die halters aanraken wil ik discipline zien. Als jij 4 sets van 12 reps bankdrukt met 60 kilo, hoeveel herhalingen heb je dan in totaal gedrukt?',
            'correct_choice' => 'A',
            'choices' => [
                'A' => '48 herhalingen in totaal',
                'B' => '44 herhalingen in totaal',
                'C' => '52 herhalingen in totaal',
            ],
            'feedback_wrong' => 'Barry lacht je uit: "Als je niet eens kunt tellen, ga je die gewichten laten vallen! Eerst je kop trainen!"',
        ],
        4 => [
            'npc_name' => 'Zuster Karin',
            'npc_avatar' => 'assets/characters/avatar_female_1.jpg',
            'npc_role' => 'Hoofdverpleegkundige Medheek',
            'dilemma' => 'De hoofdarts telt de pillen Oxazepam in het medicijnenbakje. Er horen 32 pillen in te zitten. Als ik er 25 tel, hoeveel ontbreken er op de officiële aftekenlijst?',
            'correct_choice' => 'C',
            'choices' => [
                'A' => '5 pillen ontbreken',
                'B' => '9 pillen ontbreken',
                'C' => '7 pillen ontbreken',
            ],
            'feedback_wrong' => 'Zuster Karin kijkt streng: "Dat klopt niet in het dossier! Het protocol slaat alarm."',
        ],
        5 => [
            'npc_name' => 'Dokter Debbie',
            'npc_avatar' => 'assets/characters/avatar_female_2.jpg',
            'npc_role' => 'Psychiater Paviljoen 7',
            'dilemma' => 'Tijdens het groepsoverleg vraagt de commissie om een verklaring over je kalmte. Welk gedrag levert de hoogste goedkeuring en privilegepas op?',
            'correct_choice' => 'A',
            'choices' => [
                'A' => 'Oogcontact houden, instemmend knikken en praten over positieve toekomstdoelen.',
                'B' => 'Met je vuist op tafel slaan en eisen dat ze je dossier per direct sluiten.',
                'C' => 'Je capuchon over je hoofd trekken en staren naar de vloer.',
            ],
            'feedback_wrong' => 'Dokter Debbie noteert in rood: "Onbeheerst gedrag waargenomen. Privilegepas geweigerd."',
        ],
    ];

    public function __construct(
        protected LevelService $levelService,
        protected EconomyService $economyService,
        protected EnergyService $energyService,
        protected PrisonService $prisonService,
    ) {}

    /**
     * Execute a mission with interactive scenario choice verification.
     *
     * @return array{success: bool, message: string, cash_earned: int, xp_earned: int, respect_earned: int}
     */
    public function execute(Character $character, Mission $mission, ?string $userChoice = null): array
    {
        if ($character->isImprisoned()) {
            return [
                'success' => false,
                'message' => 'Je zit in de isoleercel en kunt geen missies uitvoeren!',
                'cash_earned' => 0,
                'xp_earned' => 0,
                'respect_earned' => 0,
            ];
        }

        if ($character->level < $mission->required_level) {
            return [
                'success' => false,
                'message' => "Je moet minimaal Level {$mission->required_level} zijn voor deze missie.",
                'cash_earned' => 0,
                'xp_earned' => 0,
                'respect_earned' => 0,
            ];
        }

        // Check interactive scenario choice if defined
        $scenario = self::SCENARIOS[$mission->id] ?? null;
        if ($scenario) {
            if (empty($userChoice) && $mission->success_rate < 100) {
                return [
                    'success' => false,
                    'message' => 'Kies een tactische beslissing uit de situatie om de missie uit te voeren!',
                    'cash_earned' => 0,
                    'xp_earned' => 0,
                    'respect_earned' => 0,
                ];
            }

            $userChoice = $userChoice ?? $scenario['correct_choice'];

            if (strtoupper($userChoice) !== $scenario['correct_choice']) {
                // Incorrect choice penalty
                $this->energyService->consume($character, min(4, $mission->energy_cost));

                return [
                    'success' => false,
                    'message' => $scenario['feedback_wrong'],
                    'cash_earned' => 0,
                    'xp_earned' => 0,
                    'respect_earned' => 0,
                ];
            }
        }

        if (! $this->energyService->consume($character, $mission->energy_cost)) {
            return [
                'success' => false,
                'message' => "Niet genoeg energie! Deze missie vereist {$mission->energy_cost} energie.",
                'cash_earned' => 0,
                'xp_earned' => 0,
                'respect_earned' => 0,
            ];
        }

        // Lowered base chance for real gameplay suspense
        $baseChance = ($mission->success_rate >= 100) ? 100 : min(65, (int) round($mission->success_rate * 0.75));
        $bonus = (int) floor(($character->intelligence + $character->discipline) / 25);
        $totalChance = ($mission->success_rate >= 100) ? 100 : min(75, max(20, $baseChance + $bonus));

        $roll = random_int(1, 100);
        $isSuccess = $roll <= $totalChance;

        $progression = CharacterMission::firstOrNew([
            'character_id' => $character->id,
            'mission_id' => $mission->id,
        ]);
        $progression->last_attempted_at = now();

        if ($isSuccess) {
            $cashEarned = random_int($mission->min_cash_reward, $mission->max_cash_reward);
            $xpEarned = $mission->xp_reward;
            $respectEarned = $mission->respect_reward;

            // CRITICAL RULE: Mission rewards are strictly clean money deposited directly to the BANK!
            $this->economyService->addBankReward($character, $cashEarned);
            $this->levelService->addXp($character, $xpEarned);
            $character->respect += $respectEarned;
            $character->missions_completed++;
            $character->save();

            $progression->completed_count = ($progression->completed_count ?? 0) + 1;
            $progression->save();

            $msg = $mission->success_message ?: 'Missie succesvol afgerond! Je tactische beslissing was vlekkeloos.';
            $fullMsg = "🎯 {$msg} Beloning: ".number_format($cashEarned, 0, ',', '.')." KC wit geld direct gestort op je bankrekening 🏦, +{$xpEarned} XP en +{$respectEarned} Respect!";

            return [
                'success' => true,
                'message' => $fullMsg,
                'cash_earned' => $cashEarned,
                'xp_earned' => $xpEarned,
                'respect_earned' => $respectEarned,
            ];
        }

        $progression->save();

        // On failure: chance to get jailed for risky missions
        $failMsg = $mission->fail_message ?: 'Missie mislukt! Ondanks je juiste antwoord liep de afhandeling door onvoorziene patrouilles in het honderd.';

        if ($mission->category === 'theft' || $mission->category === 'high_risk') {
            $jailRoll = random_int(1, 100);
            if ($jailRoll <= 40) {
                $jailMinutes = random_int(3, 8);
                $this->prisonService->jail($character, $jailMinutes, "Mislukte missie: {$mission->title}");
                $failMsg .= " Bewakers grepen je in de kraag! {$jailMinutes} minuten celstraf!";
            }
        }

        return [
            'success' => false,
            'message' => $failMsg,
            'cash_earned' => 0,
            'xp_earned' => 0,
            'respect_earned' => 0,
        ];
    }
}
