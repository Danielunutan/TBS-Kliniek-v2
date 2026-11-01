<?php

namespace App\Services;

use App\Models\Character;
use App\Models\DailyBattle;
use App\Models\DailyBattleScore;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\VaultCode;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DailyBattleService
{
    /**
     * Define the seven escalation sectors and their Escalation Point (EP) conversion weights.
     *
     * @var array<string, array{
     *     id: string,
     *     title: string,
     *     subtitle: string,
     *     icon: string,
     *     column: string,
     *     ep_weight: float,
     *     formula_label: string,
     *     unit_label: string,
     *     rewards: array<int, array{type: string, amount: int, label: string}>
     * }>
     */
    public const CATEGORIES = [
        'slay' => [
            'id' => 'slay',
            'title' => 'STRAATGEZAG & EERBETOON',
            'subtitle' => 'Opgebouwde reputatie door machtsvertoon op de afdeling.',
            'icon' => 'fa-crown',
            'column' => 'score_slay',
            'ep_weight' => 3.0,
            'formula_label' => '1 Gezag = +3 EP',
            'unit_label' => 'Gezagspunten',
            'rewards' => [
                1 => ['type' => 'stars', 'amount' => 100, 'label' => '100 Sterren ⭐'],
                2 => ['type' => 'masks', 'amount' => 10, 'label' => '10 Bivakmutsen 🎭'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Bivakmutsen 🎭'],
            ],
        ],
        'hustles' => [
            'id' => 'hustles',
            'title' => 'ONDERGRONDSE ZWENDEL',
            'subtitle' => 'Geslaagde ritselacties en snelle klussen achter gesloten deuren.',
            'icon' => 'fa-sack-dollar',
            'column' => 'score_hustles',
            'ep_weight' => 15.0,
            'formula_label' => '1 Zwendel = +15 EP',
            'unit_label' => 'Zwendels',
            'rewards' => [
                1 => ['type' => 'stars', 'amount' => 100, 'label' => '100 Sterren ⭐'],
                2 => ['type' => 'masks', 'amount' => 10, 'label' => '10 Bivakmutsen 🎭'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Bivakmutsen 🎭'],
            ],
        ],
        'payments' => [
            'id' => 'payments',
            'title' => 'ZWARTGELD-CIRCULATIE',
            'subtitle' => 'Totale kapitaalstroom via aankopen en kasstortingen.',
            'icon' => 'fa-money-bill-transfer',
            'column' => 'score_payments',
            'ep_weight' => 0.004,
            'formula_label' => '€ 250 Omzet = +1 EP',
            'unit_label' => 'Euro omzet',
            'rewards' => [
                1 => ['type' => 'xxl_cases', 'amount' => 3, 'label' => '3 XXL Koffers 🧳'],
                2 => ['type' => 'xxl_cases', 'amount' => 2, 'label' => '2 XXL Koffers 🧳'],
                3 => ['type' => 'xxl_cases', 'amount' => 1, 'label' => '1 XXL Koffer 🧳'],
            ],
        ],
        'breakouts' => [
            'id' => 'breakouts',
            'title' => 'CEL-BEVRIJDINGEN',
            'subtitle' => 'Medegedetineerden die uit de isoleervleugel zijn gehaald.',
            'icon' => 'fa-unlock-keyhole',
            'column' => 'score_breakouts',
            'ep_weight' => 25.0,
            'formula_label' => '1 Uitbraak = +25 EP',
            'unit_label' => 'Bevrijdingen',
            'rewards' => [
                1 => ['type' => 'stars', 'amount' => 100, 'label' => '100 Sterren ⭐'],
                2 => ['type' => 'masks', 'amount' => 10, 'label' => '10 Bivakmutsen 🎭'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Bivakmutsen 🎭'],
            ],
        ],
        'drugs_made' => [
            'id' => 'drugs_made',
            'title' => 'LAB-SYNTHESE',
            'subtitle' => 'Vervaardigde eenheden smokkelwaar in de verborgen stokerij.',
            'icon' => 'fa-flask-vial',
            'column' => 'score_drugs_made',
            'ep_weight' => 10.0,
            'formula_label' => '1 Batch = +10 EP',
            'unit_label' => 'Batches',
            'rewards' => [
                1 => ['type' => 'xxl_cases', 'amount' => 2, 'label' => '2 XXL Koffers 🧳'],
                2 => ['type' => 'vault_codes', 'amount' => 10, 'label' => '10 Kluiscodes 🔐'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Bivakmutsen 🎭'],
            ],
        ],
        'drugs_sold' => [
            'id' => 'drugs_sold',
            'title' => 'CONTRABANDE-AFZET',
            'subtitle' => 'Afgeronde smokkeldeals met afnemers binnen het complex.',
            'icon' => 'fa-boxes-packing',
            'column' => 'score_drugs_sold',
            'ep_weight' => 12.0,
            'formula_label' => '1 Levering = +12 EP',
            'unit_label' => 'Leveringen',
            'rewards' => [
                1 => ['type' => 'masks', 'amount' => 20, 'label' => '20 Bivakmutsen 🎭'],
                2 => ['type' => 'vault_codes', 'amount' => 10, 'label' => '10 Kluiscodes 🔐'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Bivakmutsen 🎭'],
            ],
        ],
        'kills' => [
            'id' => 'kills',
            'title' => 'GENADELOZE LIQUIDATIES',
            'subtitle' => 'Uitgeschakelde tegenstanders tijdens harde confrontaties.',
            'icon' => 'fa-crosshairs',
            'column' => 'score_kills',
            'ep_weight' => 50.0,
            'formula_label' => '1 Uitschakeling = +50 EP',
            'unit_label' => 'Uitschakelingen',
            'rewards' => [
                1 => ['type' => 'xxl_cases', 'amount' => 2, 'label' => '2 XXL Koffers 🧳'],
                2 => ['type' => 'vault_codes', 'amount' => 10, 'label' => '10 Kluiscodes 🔐'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Bivakmutsen 🎭'],
            ],
        ],
    ];

    /**
     * Define personal daily escalation milestones and their rewards.
     *
     * @var array<int, array{
     *     tier: int,
     *     title: string,
     *     required_ep: int,
     *     cash: int,
     *     masks: int,
     *     stars: int,
     *     xxl_cases: int,
     *     label: string
     * }>
     */
    public const MILESTONES = [
        1 => [
            'tier' => 1,
            'title' => 'Vleugel-Onruststoker',
            'required_ep' => 100,
            'cash' => 15000,
            'masks' => 2,
            'stars' => 0,
            'xxl_cases' => 0,
            'label' => '€ 15.000 + 2 Bivakmutsen 🎭',
        ],
        2 => [
            'tier' => 2,
            'title' => 'Paviljoen-Aanjager',
            'required_ep' => 350,
            'cash' => 45000,
            'masks' => 5,
            'stars' => 5,
            'xxl_cases' => 0,
            'label' => '€ 45.000 + 5 Bivakmutsen 🎭 + 5 Sterren ⭐',
        ],
        3 => [
            'tier' => 3,
            'title' => 'Etmaal-Opperhoofd',
            'required_ep' => 900,
            'cash' => 100000,
            'masks' => 0,
            'stars' => 10,
            'xxl_cases' => 1,
            'label' => '€ 100.000 + 10 Sterren ⭐ + 1 XXL Koffer 🧳',
        ],
    ];

    /**
     * Map incoming category aliases to canonical category keys.
     */
    public function resolveCategoryKey(string $category): ?string
    {
        $keyMap = [
            'slay' => 'slay',
            'respect' => 'slay',
            'hustles' => 'hustles',
            'crimes' => 'hustles',
            'payments' => 'payments',
            'spent' => 'payments',
            'donations' => 'payments',
            'breakouts' => 'breakouts',
            'drugs_made' => 'drugs_made',
            'production' => 'drugs_made',
            'drugs_sold' => 'drugs_sold',
            'sales' => 'drugs_sold',
            'kills' => 'kills',
            'fights' => 'kills',
        ];

        return $keyMap[$category] ?? null;
    }

    /**
     * Determine whether the current time falls inside a clinical rush hour window.
     */
    public function isRushHour(?Carbon $now = null): bool
    {
        $tz = config('app.timezone', 'Europe/Amsterdam');
        $checkTime = $now ? $now->copy()->setTimezone($tz) : Carbon::now($tz);
        $hour = (int) $checkTime->format('G');

        return ($hour >= 12 && $hour < 14) || ($hour >= 20 && $hour < 22);
    }

    /**
     * Build status details for the rush hour multiplier display.
     *
     * @return array{isActive: bool, focusMultiplier: float, windowLabel: string, statusText: string}
     */
    public function getRushHourStatus(?Carbon $now = null): array
    {
        $isActive = $this->isRushHour($now);

        return [
            'isActive' => $isActive,
            'focusMultiplier' => $isActive ? 2.0 : 1.5,
            'windowLabel' => '12:00–14:00 & 20:00–22:00',
            'statusText' => $isActive
                ? 'Kliniek-Spitsuur actief: je vergrendelde Focus-Sector levert nu 2.0x score op!'
                : 'Buiten spitsuur: je vergrendelde Focus-Sector levert standaard 1.5x score op (2.0x tijdens spitsuur).',
        ];
    }

    /**
     * Return the active focus category key for a character today.
     */
    public function getCharacterFocus(Character $character, ?DailyBattle $battle = null): ?string
    {
        $todayBattle = $battle ?? $this->getOrCreateTodayBattle();
        $meta = is_array($todayBattle->winners_data) ? $todayBattle->winners_data : [];
        $focusMap = $meta['_focus'] ?? [];

        if (! is_array($focusMap)) {
            return null;
        }

        $selected = $focusMap[(string) $character->id] ?? null;

        return is_string($selected) && isset(self::CATEGORIES[$selected]) ? $selected : null;
    }

    /**
     * Lock in the daily focus discipline for a character.
     *
     * @return array{success: bool, message: string}
     */
    public function selectFocusCategory(Character $character, string $category): array
    {
        if (! isset(self::CATEGORIES[$category])) {
            return [
                'success' => false,
                'message' => 'Gekozen escalatie-sector bestaat niet.',
            ];
        }

        $today = Carbon::today()->format('Y-m-d');
        $this->getOrCreateTodayBattle();

        return DB::transaction(function () use ($character, $category, $today): array {
            /** @var DailyBattle|null $lockedBattle */
            $lockedBattle = DailyBattle::whereDate('battle_date', $today)->lockForUpdate()->first();
            if (! $lockedBattle) {
                return [
                    'success' => false,
                    'message' => 'Etmaal-registratie kon niet worden geladen.',
                ];
            }

            $data = is_array($lockedBattle->winners_data) ? $lockedBattle->winners_data : [];
            $focusMap = isset($data['_focus']) && is_array($data['_focus']) ? $data['_focus'] : [];
            $charKey = (string) $character->id;

            if (isset($focusMap[$charKey])) {
                $currentTitle = self::CATEGORIES[$focusMap[$charKey]]['title'] ?? $focusMap[$charKey];

                return [
                    'success' => false,
                    'message' => "Je Focus-Overdrive staat vandaag al vast op {$currentTitle}.",
                ];
            }

            $focusMap[$charKey] = $category;
            $data['_focus'] = $focusMap;
            $lockedBattle->update(['winners_data' => $data]);

            $chosenTitle = self::CATEGORIES[$category]['title'];

            return [
                'success' => true,
                'message' => "Focus-Overdrive geactiveerd op {$chosenTitle}! Je pakt nu 1.5x score (en 2.0x tijdens het spitsuur).",
            ];
        });
    }

    /**
     * Calculate Escalation Points (EP) for one category score.
     */
    public function calculateCategoryEp(string $categoryKey, int $rawScore): int
    {
        if ($rawScore <= 0 || ! isset(self::CATEGORIES[$categoryKey])) {
            return 0;
        }

        $weight = self::CATEGORIES[$categoryKey]['ep_weight'];

        return (int) floor($rawScore * $weight);
    }

    /**
     * Calculate total Escalation Points (EP) and per-category breakdown for a score row.
     *
     * @return array{totalEp: int, byCategory: array<string, int>}
     */
    public function calculateCharacterEp(?DailyBattleScore $scoreRecord): array
    {
        $totalEp = 0;
        $byCategory = [];

        foreach (self::CATEGORIES as $key => $cat) {
            $col = $cat['column'];
            $raw = $scoreRecord ? (int) ($scoreRecord->{$col} ?? 0) : 0;
            $ep = $this->calculateCategoryEp($key, $raw);
            $byCategory[$key] = $ep;
            $totalEp += $ep;
        }

        return [
            'totalEp' => $totalEp,
            'byCategory' => $byCategory,
        ];
    }

    /**
     * Claim a personal daily escalation milestone reward.
     *
     * @return array{success: bool, message: string}
     */
    public function claimMilestone(Character $character, int $tier): array
    {
        if (! isset(self::MILESTONES[$tier])) {
            return [
                'success' => false,
                'message' => 'Onbekende mijlpaal-trede geselecteerd.',
            ];
        }

        $milestone = self::MILESTONES[$tier];
        $today = Carbon::today()->format('Y-m-d');
        $this->getOrCreateTodayBattle();

        return DB::transaction(function () use ($character, $tier, $milestone, $today): array {
            /** @var DailyBattle|null $lockedBattle */
            $lockedBattle = DailyBattle::whereDate('battle_date', $today)->lockForUpdate()->first();
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $lockedBattle || ! $lockedChar) {
                return [
                    'success' => false,
                    'message' => 'Kan mijlpaal niet verwerken op dit moment.',
                ];
            }

            $scoreRecord = DailyBattleScore::whereDate('battle_date', $today)
                ->where('character_id', $lockedChar->id)
                ->first();

            $epData = $this->calculateCharacterEp($scoreRecord);
            $totalEp = $epData['totalEp'];

            if ($totalEp < $milestone['required_ep']) {
                $shortfall = $milestone['required_ep'] - $totalEp;

                return [
                    'success' => false,
                    'message' => "Je komt nog {$shortfall} EP tekort voor mijlpaal '{$milestone['title']}'.",
                ];
            }

            $data = is_array($lockedBattle->winners_data) ? $lockedBattle->winners_data : [];
            $milestonesMap = isset($data['_milestones']) && is_array($data['_milestones']) ? $data['_milestones'] : [];
            $charKey = (string) $lockedChar->id;
            $claimedTiers = isset($milestonesMap[$charKey]) && is_array($milestonesMap[$charKey])
                ? array_map('intval', $milestonesMap[$charKey])
                : [];

            if (in_array($tier, $claimedTiers, true)) {
                return [
                    'success' => false,
                    'message' => "Mijlpaal '{$milestone['title']}' is vandaag al door jou opgeëist.",
                ];
            }

            $lockedChar->cash = ($lockedChar->cash ?? 0) + $milestone['cash'];
            $lockedChar->masks = ($lockedChar->masks ?? 0) + $milestone['masks'];
            $lockedChar->stars = ($lockedChar->stars ?? 0) + $milestone['stars'];
            $lockedChar->xxl_cases = ($lockedChar->xxl_cases ?? 0) + $milestone['xxl_cases'];
            $lockedChar->save();

            $claimedTiers[] = $tier;
            $milestonesMap[$charKey] = array_values(array_unique($claimedTiers));
            $data['_milestones'] = $milestonesMap;
            $lockedBattle->update(['winners_data' => $data]);

            return [
                'success' => true,
                'message' => "Mijlpaal '{$milestone['title']}' vrijgespeeld! Beloning ontvangen: {$milestone['label']}.",
            ];
        });
    }

    /**
     * Record a score increment for a character in today's battle.
     */
    public function record(Character $character, string $category, int $amount = 1, ?Carbon $now = null): void
    {
        if ($amount <= 0) {
            return;
        }

        $canonicalKey = $this->resolveCategoryKey($category);
        if (! $canonicalKey || ! isset(self::CATEGORIES[$canonicalKey])) {
            return;
        }

        $col = self::CATEGORIES[$canonicalKey]['column'];
        $today = Carbon::today()->format('Y-m-d');

        $effectiveAmount = $amount;
        $todayBattle = DailyBattle::whereDate('battle_date', $today)->first();
        if ($todayBattle) {
            $focusKey = $this->getCharacterFocus($character, $todayBattle);
            if ($focusKey === $canonicalKey) {
                $multiplier = $this->isRushHour($now) ? 2.0 : 1.5;
                $effectiveAmount = (int) ceil($amount * $multiplier);
            }
        }

        $score = DailyBattleScore::whereDate('battle_date', $today)
            ->where('character_id', $character->id)
            ->first();

        if (! $score) {
            try {
                $score = DailyBattleScore::firstOrCreate(
                    [
                        'battle_date' => $today,
                        'character_id' => $character->id,
                    ],
                    [
                        'score_slay' => 0,
                        'score_hustles' => 0,
                        'score_payments' => 0,
                        'score_breakouts' => 0,
                        'score_drugs_made' => 0,
                        'score_drugs_sold' => 0,
                        'score_kills' => 0,
                    ]
                );
            } catch (QueryException) {
                $score = DailyBattleScore::whereDate('battle_date', $today)
                    ->where('character_id', $character->id)
                    ->first();
            }
        }

        $score?->increment($col, $effectiveAmount);
    }

    /**
     * Get or create today's active battle.
     */
    public function getOrCreateTodayBattle(): DailyBattle
    {
        $today = Carbon::today()->format('Y-m-d');

        $battle = DailyBattle::whereDate('battle_date', $today)->first();
        if (! $battle) {
            $battle = DailyBattle::create([
                'battle_date' => $today,
                'title' => 'Etmaal-Escalatie Protocol ⚡',
                'is_settled' => false,
            ]);
        }

        return $battle;
    }

    /**
     * Check if previous days have unsettled battles and settle them with prize distribution.
     */
    public function checkAndSettle(): void
    {
        $today = Carbon::today()->toDateString();

        $unsettledBattles = DailyBattle::where('battle_date', '<', $today)
            ->where('is_settled', false)
            ->get();

        foreach ($unsettledBattles as $battle) {
            // Lock the battle row and verify the flag inside a transaction to prevent duplicate payouts.
            DB::transaction(function () use ($battle): void {
                $lockedBattle = DailyBattle::where('id', $battle->id)->lockForUpdate()->first();
                if (! $lockedBattle || $lockedBattle->is_settled) {
                    return;
                }

                $this->settleBattle($lockedBattle);
            });
        }
    }

    /**
     * Settle a specific battle and grant prizes to the top 3 players in each sector.
     */
    public function settleBattle(DailyBattle $battle): void
    {
        $existingData = is_array($battle->winners_data) ? $battle->winners_data : [];
        $winnersLog = [];

        if (isset($existingData['_focus'])) {
            $winnersLog['_focus'] = $existingData['_focus'];
        }
        if (isset($existingData['_milestones'])) {
            $winnersLog['_milestones'] = $existingData['_milestones'];
        }

        foreach (self::CATEGORIES as $key => $cat) {
            $col = $cat['column'];

            $battleDate = Carbon::parse($battle->battle_date)->format('Y-m-d');
            $topScores = DailyBattleScore::whereDate('battle_date', $battleDate)
                ->where($col, '>', 0)
                ->orderByDesc($col)
                ->orderBy('id')
                ->take(3)
                ->with('character')
                ->get();

            $winnersLog[$key] = [];
            $rank = 0;

            foreach ($topScores as $scoreRecord) {
                $char = $scoreRecord->character;
                if (! $char) {
                    continue;
                }

                $rank++;
                $reward = $cat['rewards'][$rank] ?? null;
                if (! $reward) {
                    continue;
                }

                $this->awardPrize($char, $reward['type'], $reward['amount'], $cat['title'], $rank);

                $winnersLog[$key][] = [
                    'rank' => $rank,
                    'character_id' => $char->id,
                    'character_name' => $char->name,
                    'score' => $scoreRecord->{$col},
                    'reward' => $reward['label'],
                ];
            }
        }

        $battle->update([
            'is_settled' => true,
            'settled_at' => now(),
            'winners_data' => $winnersLog,
        ]);
    }

    /**
     * Award a specific prize to a character.
     */
    protected function awardPrize(Character $character, string $type, int $amount, string $categoryTitle, int $rank): void
    {
        /** @var Character|null $locked */
        $locked = Character::where('id', $character->id)->lockForUpdate()->first();
        if (! $locked) {
            return;
        }

        if ($type === 'stars') {
            $locked->stars = ($locked->stars ?? 0) + $amount;
        } elseif ($type === 'masks') {
            $locked->masks = ($locked->masks ?? 0) + $amount;
        } elseif ($type === 'xxl_cases') {
            $locked->xxl_cases = ($locked->xxl_cases ?? 0) + $amount;
        } elseif ($type === 'vault_codes') {
            for ($i = 0; $i < $amount; $i++) {
                VaultCode::create([
                    'code' => VaultCode::generateUniqueCode('BATTLE'),
                    'reward_cash' => rand(2500, 7500),
                    'reward_stars' => (rand(1, 100) <= 25 ? 1 : 0),
                    'reward_tokens' => 3,
                    'character_id' => $locked->id,
                    'is_redeemed' => false,
                ]);
            }
        }
        $locked->save();

        $typeLabel = match ($type) {
            'stars' => 'Sterren ⭐',
            'masks' => 'Bivakmutsen 🎭',
            'xxl_cases' => 'XXL Koffers 🧳',
            'vault_codes' => 'Kluiscodes 🔑',
            default => $type,
        };

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_prize',
            'icon' => 'fa-bolt',
            'title' => "⚡ Etmaal-Escalatie Uitkering: Rang #{$rank} bij {$categoryTitle}",
            'message' => "De klok is rond! Met jouw prestatie behaalde je positie #{$rank} binnen de sector '{$categoryTitle}'. Jouw premie ({$amount} {$typeLabel}) staat nu klaar op je account.",
        ]);

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_name' => 'Kliniek Escalatie-Tribunaal',
            'subject' => "⚡ Eindafrekening Etmaal-Protocol: Rang #{$rank} ({$categoryTitle})",
            'content' => "Gedetineerde {$character->name},\n\nHet afgelopen etmaal is afgesloten en de balans is opgemaakt. Binnen de discipline **{$categoryTitle}** heb jij **Klassering #{$rank}** weten te bemachtigen.\n\nDe bijbehorende buit ({$amount} {$typeLabel}) is direct aan je inventaris of saldo gekoppeld.\n\nKies vandaag opnieuw je Focus-Sector en verdedig je positie!",
            'type' => 'system',
        ]);
    }

    /**
     * Get complete battle display data for the character view.
     *
     * @return array{
     *     battle: DailyBattle,
     *     categories: array<string, mixed>,
     *     secondsUntilReset: int,
     *     resetTimeFormatted: string,
     *     rushHour: array{isActive: bool, focusMultiplier: float, windowLabel: string, statusText: string},
     *     focusCategory: string|null,
     *     totalEp: int,
     *     milestones: array<int, array<string, mixed>>
     * }
     */
    public function getBattleData(Character $character): array
    {
        $this->checkAndSettle();
        $battle = $this->getOrCreateTodayBattle();
        $today = Carbon::today()->toDateString();

        $focusCategory = $this->getCharacterFocus($character, $battle);
        $rushHour = $this->getRushHourStatus();

        $charScoreRow = DailyBattleScore::whereDate('battle_date', $today)
            ->where('character_id', $character->id)
            ->first();

        $epSummary = $this->calculateCharacterEp($charScoreRow);
        $totalEp = $epSummary['totalEp'];

        $battleMeta = is_array($battle->winners_data) ? $battle->winners_data : [];
        $claimedMap = isset($battleMeta['_milestones']) && is_array($battleMeta['_milestones']) ? $battleMeta['_milestones'] : [];
        $charClaimed = isset($claimedMap[(string) $character->id]) && is_array($claimedMap[(string) $character->id])
            ? array_map('intval', $claimedMap[(string) $character->id])
            : [];

        $milestonesData = [];
        foreach (self::MILESTONES as $tier => $ms) {
            $isClaimed = in_array($tier, $charClaimed, true);
            $isUnlocked = $totalEp >= $ms['required_ep'];
            $milestonesData[$tier] = array_merge($ms, [
                'is_claimed' => $isClaimed,
                'is_unlocked' => $isUnlocked,
                'can_claim' => $isUnlocked && ! $isClaimed,
                'progress_pct' => min(100, (int) floor(($totalEp / max(1, $ms['required_ep'])) * 100)),
            ]);
        }

        $categoriesData = [];

        foreach (self::CATEGORIES as $key => $cat) {
            $col = $cat['column'];

            $scores = DailyBattleScore::whereDate('battle_date', $today)
                ->where($col, '>', 0)
                ->orderByDesc($col)
                ->with('character')
                ->get();

            $userPos = null;
            $userScore = 0;

            foreach ($scores as $index => $s) {
                if ($s->character_id === $character->id) {
                    $userPos = $index + 1;
                    $userScore = (int) $s->{$col};
                    break;
                }
            }

            $userEp = $this->calculateCategoryEp($key, $userScore);

            $top3 = $scores->take(3)->map(function ($s, $idx) use ($col, $key) {
                $rawVal = (int) $s->{$col};

                return [
                    'rank' => $idx + 1,
                    'name' => $s->character->name ?? 'Onbekend',
                    'avatar' => $s->character?->avatar_url ?? asset('assets/characters/avatar_female_1.jpg'),
                    'score' => $rawVal,
                    'ep' => $this->calculateCategoryEp($key, $rawVal),
                ];
            })->values()->toArray();

            $categoriesData[$key] = [
                'id' => $cat['id'],
                'title' => $cat['title'],
                'subtitle' => $cat['subtitle'],
                'icon' => $cat['icon'],
                'column' => $col,
                'ep_weight' => $cat['ep_weight'],
                'formula_label' => $cat['formula_label'],
                'unit_label' => $cat['unit_label'],
                'is_focus' => $focusCategory === $key,
                'rewards' => $cat['rewards'],
                'top3' => $top3,
                'userPos' => $userPos,
                'userScore' => $userScore,
                'userEp' => $userEp,
            ];
        }

        $tz = config('app.timezone', 'Europe/Amsterdam');
        $now = Carbon::now($tz);
        $endOfDay = Carbon::today($tz)->endOfDay();
        $secondsUntilReset = max(0, (int) round($now->diffInSeconds($endOfDay, false)));
        $hours = floor($secondsUntilReset / 3600);
        $minutes = floor(($secondsUntilReset % 3600) / 60);
        $seconds = $secondsUntilReset % 60;
        $resetTimeFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        return [
            'battle' => $battle,
            'categories' => $categoriesData,
            'secondsUntilReset' => $secondsUntilReset,
            'resetTimeFormatted' => $resetTimeFormatted,
            'rushHour' => $rushHour,
            'focusCategory' => $focusCategory,
            'totalEp' => $totalEp,
            'milestones' => $milestonesData,
        ];
    }
}
