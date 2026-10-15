<?php

namespace App\Services;

use App\Models\Character;
use App\Models\DailyBattle;
use App\Models\DailyBattleScore;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\VaultCode;
use Carbon\Carbon;

class DailyBattleService
{
    /**
     * Categories in the 24 Hour Battle matching screenshot.
     */
    public const CATEGORIES = [
        'slay' => [
            'id' => 'slay',
            'title' => 'MEESTE RESPECT',
            'icon' => 'fa-fire',
            'column' => 'score_slay',
            'rewards' => [
                1 => ['type' => 'stars', 'amount' => 100, 'label' => '100 Sterren ⭐'],
                2 => ['type' => 'masks', 'amount' => 10, 'label' => '10 Maskers 🎭'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Maskers 🎭'],
            ],
        ],
        'hustles' => [
            'id' => 'hustles',
            'title' => 'MEESTE HOSSELS',
            'icon' => 'fa-handshake',
            'column' => 'score_hustles',
            'rewards' => [
                1 => ['type' => 'stars', 'amount' => 100, 'label' => '100 Sterren ⭐'],
                2 => ['type' => 'masks', 'amount' => 10, 'label' => '10 Maskers 🎭'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Maskers 🎭'],
            ],
        ],
        'payments' => [
            'id' => 'payments',
            'title' => 'BETALINGEN',
            'icon' => 'fa-cart-shopping',
            'column' => 'score_payments',
            'rewards' => [
                1 => ['type' => 'xxl_cases', 'amount' => 3, 'label' => '3 XXL koffers 🧳'],
                2 => ['type' => 'xxl_cases', 'amount' => 2, 'label' => '2 XXL koffers 🧳'],
                3 => ['type' => 'xxl_cases', 'amount' => 1, 'label' => '1 XXL koffer 🧳'],
            ],
        ],
        'breakouts' => [
            'id' => 'breakouts',
            'title' => 'PATIËNTEN UITGEBROKEN',
            'icon' => 'fa-dungeon',
            'column' => 'score_breakouts',
            'rewards' => [
                1 => ['type' => 'stars', 'amount' => 100, 'label' => '100 Sterren ⭐'],
                2 => ['type' => 'masks', 'amount' => 10, 'label' => '10 Maskers 🎭'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Maskers 🎭'],
            ],
        ],
        'drugs_made' => [
            'id' => 'drugs_made',
            'title' => 'DRUGS GEMAAKT',
            'icon' => 'fa-cannabis',
            'column' => 'score_drugs_made',
            'rewards' => [
                1 => ['type' => 'xxl_cases', 'amount' => 2, 'label' => '2 XXL koffers 🧳'],
                2 => ['type' => 'vault_codes', 'amount' => 10, 'label' => '10 Kluiscodes 🔐'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Maskers 🎭'],
            ],
        ],
        'drugs_sold' => [
            'id' => 'drugs_sold',
            'title' => 'DRUGS VERKOCHT',
            'icon' => 'fa-capsules',
            'column' => 'score_drugs_sold',
            'rewards' => [
                1 => ['type' => 'masks', 'amount' => 20, 'label' => '20 Maskers 🎭'],
                2 => ['type' => 'vault_codes', 'amount' => 10, 'label' => '10 Kluiscodes 🔐'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Maskers 🎭'],
            ],
        ],
        'kills' => [
            'id' => 'kills',
            'title' => 'MEESTE MOORDEN',
            'icon' => 'fa-skull',
            'column' => 'score_kills',
            'rewards' => [
                1 => ['type' => 'xxl_cases', 'amount' => 2, 'label' => '2 XXL koffers 🧳'],
                2 => ['type' => 'vault_codes', 'amount' => 10, 'label' => '10 Kluiscodes 🔐'],
                3 => ['type' => 'masks', 'amount' => 5, 'label' => '5 Maskers 🎭'],
            ],
        ],
    ];

    /**
     * Record a score increment for a character in today's battle.
     */
    public function record(Character $character, string $category, int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        $columnMap = [
            'slay' => 'score_slay',
            'respect' => 'score_slay',
            'hustles' => 'score_hustles',
            'payments' => 'score_payments',
            'spent' => 'score_payments',
            'donations' => 'score_payments',
            'breakouts' => 'score_breakouts',
            'drugs_made' => 'score_drugs_made',
            'production' => 'score_drugs_made',
            'drugs_sold' => 'score_drugs_sold',
            'sales' => 'score_drugs_sold',
            'kills' => 'score_kills',
            'fights' => 'score_kills',
        ];

        if (! isset($columnMap[$category])) {
            return;
        }

        $col = $columnMap[$category];
        $today = Carbon::today()->format('Y-m-d');

        $score = DailyBattleScore::whereDate('battle_date', $today)
            ->where('character_id', $character->id)
            ->first();

        if (! $score) {
            $score = DailyBattleScore::create([
                'battle_date' => $today,
                'character_id' => $character->id,
                'score_slay' => 0,
                'score_hustles' => 0,
                'score_payments' => 0,
                'score_breakouts' => 0,
                'score_drugs_made' => 0,
                'score_drugs_sold' => 0,
                'score_kills' => 0,
            ]);
        }

        $score->increment($col, $amount);
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
                'title' => 'Strijd Om De Stack 🏆',
                'is_settled' => false,
            ]);
        }

        return $battle;
    }

    /**
     * Check if previous days have unsettled battles and automatically settle them & award prizes.
     */
    public function checkAndSettle(): void
    {
        $today = Carbon::today()->toDateString();

        $unsettledBattles = DailyBattle::where('battle_date', '<', $today)
            ->where('is_settled', false)
            ->get();

        foreach ($unsettledBattles as $battle) {
            $this->settleBattle($battle);
        }
    }

    /**
     * Settle a specific battle, granting prizes to top 3 in every category.
     */
    public function settleBattle(DailyBattle $battle): void
    {
        $winnersLog = [];

        foreach (self::CATEGORIES as $key => $cat) {
            $col = $cat['column'];

            $battleDate = Carbon::parse($battle->battle_date)->format('Y-m-d');
            $topScores = DailyBattleScore::whereDate('battle_date', $battleDate)
                ->where($col, '>', 0)
                ->orderByDesc($col)
                ->take(3)
                ->with('character')
                ->get();

            $winnersLog[$key] = [];

            foreach ($topScores as $index => $scoreRecord) {
                $rank = $index + 1;
                $char = $scoreRecord->character;
                if (! $char) {
                    continue;
                }

                $reward = $cat['rewards'][$rank] ?? null;
                if (! $reward) {
                    continue;
                }

                // Award prize to character
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
        if ($type === 'stars') {
            $character->stars = ($character->stars ?? 0) + $amount;
        } elseif ($type === 'masks') {
            $character->masks = ($character->masks ?? 0) + $amount;
        } elseif ($type === 'xxl_cases') {
            $character->xxl_cases = ($character->xxl_cases ?? 0) + $amount;
        } elseif ($type === 'vault_codes') {
            for ($i = 0; $i < $amount; $i++) {
                VaultCode::create([
                    'code' => 'BATTLE-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6)),
                    'reward_cash' => rand(2500, 7500),
                    'reward_stars' => 1,
                    'reward_tokens' => 3,
                    'character_id' => $character->id,
                    'is_redeemed' => false,
                ]);
            }
        }
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_prize',
            'icon' => 'fa-trophy',
            'title' => "🏆 24 Uur Battle Beloning: #{$rank} in {$categoryTitle}!",
            'message' => "Gefeliciteerd! Je bent geëindigd op plek #{$rank} in '{$categoryTitle}'. Je beloning ({$amount} {$type}) is direct bijgeschreven!",
        ]);

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_name' => 'TBS 24-Uur Toernooicommissie',
            'subject' => "🏆 24 Uur Battle Gewonnen: Plek #{$rank} in {$categoryTitle}!",
            'content' => "Beste patiënt {$character->name},\n\nJe hebt gisteren gestreden als een baas! In de dagelijkse battle ben je geëindigd op **Plek #{$rank}** in de categorie '{$categoryTitle}'.\n\nJouw beloning ({$amount} {$type}) is zojuist direct toegevoegd aan je profiel.\n\nBlijf strijden in de nieuwe 24 Uur Battle van vandaag!",
            'type' => 'system',
        ]);
    }

    /**
     * Get complete battle display data for character view.
     *
     * @return array{battle: DailyBattle, categories: array<string, mixed>, secondsUntilReset: int, resetTimeFormatted: string}
     */
    public function getBattleData(Character $character): array
    {
        $this->checkAndSettle();
        $battle = $this->getOrCreateTodayBattle();
        $today = Carbon::today()->toDateString();

        $categoriesData = [];

        foreach (self::CATEGORIES as $key => $cat) {
            $col = $cat['column'];

            // Get all sorted scores for this category
            $scores = DailyBattleScore::whereDate('battle_date', $today)
                ->where($col, '>', 0)
                ->orderByDesc($col)
                ->with('character')
                ->get();

            // Find character's position
            $userPos = null;
            $userScore = 0;

            foreach ($scores as $index => $s) {
                if ($s->character_id === $character->id) {
                    $userPos = $index + 1;
                    $userScore = $s->{$col};
                    break;
                }
            }

            // Top 3
            $top3 = $scores->take(3)->map(function ($s, $idx) use ($col) {
                return [
                    'rank' => $idx + 1,
                    'name' => $s->character->name ?? 'Anoniem',
                    'avatar' => $s->character?->avatar_url ?? asset('assets/characters/avatar_female_1.jpg'),
                    'score' => $s->{$col},
                ];
            })->values()->toArray();

            $categoriesData[$key] = [
                'id' => $cat['id'],
                'title' => $cat['title'],
                'icon' => $cat['icon'],
                'column' => $col,
                'rewards' => $cat['rewards'],
                'top3' => $top3,
                'userPos' => $userPos,
                'userScore' => $userScore,
            ];
        }

        // Time until midnight reset in Dutch local timezone
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
        ];
    }
}
