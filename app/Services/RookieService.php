<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class RookieService
{
    /**
     * Definitions of the 5 Rookie Overlevingstaken.
     *
     * @var array<string, array{title: string, desc: string, icon: string, route: string, route_label: string, reward_label: string}>
     */
    public const TASKS = [
        'first_theft' => [
            'title' => 'Eerste Geslaagde Misdaad',
            'desc' => 'Pleeg minimaal 1 succesvolle kruimeldiefstal in de kliniek.',
            'icon' => 'fa-mask',
            'route' => 'theft.index',
            'route_label' => 'Naar Diefstal',
            'reward_label' => '€1.000 Cash + 15 XP',
        ],
        'bank_deposit' => [
            'title' => 'Geld Veiligstellen op de Bank',
            'desc' => 'Stort je buit veilig op de bankrekening (minimaal €500 op de bank).',
            'icon' => 'fa-building-columns',
            'route' => 'bank.index',
            'route_label' => 'Naar de Bank',
            'reward_label' => '5 Sigaretten + €500 Bonus',
        ],
        'training' => [
            'title' => 'Krachttraining in de Recreatiezaal',
            'desc' => 'Train minimaal 1 keer Kracht, Verdediging of Conditie (statwaarde minimaal 11).',
            'icon' => 'fa-dumbbell',
            'route' => 'training.index',
            'route_label' => 'Naar de Gym',
            'reward_label' => '+1 Kracht & +1 Verdediging',
        ],
        'first_fight' => [
            'title' => 'Luchtplaats Doop',
            'desc' => 'Vecht minimaal 1 robbertje op de Luchtplaats voor respect en reputatie.',
            'icon' => 'fa-hand-fist',
            'route' => 'fight.index',
            'route_label' => 'Naar de Luchtplaats',
            'reward_label' => '30 Kogels + 25 XP',
        ],
        'sponsor_vip' => [
            'title' => 'Kliniek VIP Status',
            'desc' => 'Bekijk 1 gratis sponsor video in de sponsorkamer of activeer je VIP status.',
            'icon' => 'fa-crown',
            'route' => 'sponsor.index',
            'route_label' => 'Sponsorkamer',
            'reward_label' => '1 Dag Gratis VIP',
        ],
    ];

    /**
     * Get all rookie quest data with real-time completion & claim status.
     *
     * @return array{
     *     tasks: array<string, array<string, mixed>>,
     *     completed_count: int,
     *     total_count: int,
     *     progress_pct: int,
     *     all_completed: bool,
     *     grand_claimed: bool,
     *     tour_completed: bool
     * }
     */
    public function getQuests(Character $character): array
    {
        $perks = $character->perks ?? [];
        $claimed = $perks['rookie_rewards_claimed'] ?? [];
        $tourCompleted = (bool) ($perks['intake_tour_completed'] ?? false);
        $grandClaimed = (bool) ($perks['rookie_grand_claimed'] ?? false);

        $tasks = [];
        $completedCount = 0;

        foreach (self::TASKS as $key => $meta) {
            $isMet = $this->isTaskConditionMet($character, $key);
            $isClaimed = in_array($key, $claimed, true);

            if ($isMet) {
                $completedCount++;
            }

            $tasks[$key] = [
                'key' => $key,
                'title' => $meta['title'],
                'desc' => $meta['desc'],
                'icon' => $meta['icon'],
                'route' => route($meta['route']),
                'route_label' => $meta['route_label'],
                'reward_label' => $meta['reward_label'],
                'is_met' => $isMet,
                'is_claimed' => $isClaimed,
                'can_claim' => $isMet && ! $isClaimed,
            ];
        }

        $totalCount = count(self::TASKS);
        $progressPct = (int) round(($completedCount / $totalCount) * 100);
        $allCompleted = $completedCount >= $totalCount;

        return [
            'tasks' => $tasks,
            'completed_count' => $completedCount,
            'total_count' => $totalCount,
            'progress_pct' => $progressPct,
            'all_completed' => $allCompleted,
            'grand_claimed' => $grandClaimed,
            'tour_completed' => $tourCompleted,
        ];
    }

    /**
     * Check if a specific task condition is met.
     */
    public function isTaskConditionMet(Character $character, string $key): bool
    {
        return match ($key) {
            'first_theft' => (int) ($character->thefts_succeeded ?? 0) >= 1,
            'bank_deposit' => (float) ($character->bank ?? 0) >= 500,
            'training' => max((int) $character->strength, (int) $character->defense, (int) $character->stamina) >= 11,
            'first_fight' => ((int) $character->fights_won + (int) $character->fights_lost) >= 1,
            'sponsor_vip' => (int) ($character->ads_watched_total ?? 0) >= 1 || (bool) $character->is_vip,
            default => false,
        };
    }

    /**
     * Claim reward for a specific task.
     *
     * @return array{success: bool, message: string, reward: string, grand_unlocked?: bool}
     */
    public function claimTaskReward(Character $character, string $taskKey): array
    {
        if (! isset(self::TASKS[$taskKey])) {
            return ['success' => false, 'message' => 'Onbekende rookie taak.', 'reward' => ''];
        }

        if (! $this->isTaskConditionMet($character, $taskKey)) {
            return ['success' => false, 'message' => 'Je hebt de voorwaarden van deze taak nog niet behaald!', 'reward' => ''];
        }

        $perks = $character->perks ?? [];
        $claimed = $perks['rookie_rewards_claimed'] ?? [];

        if (in_array($taskKey, $claimed, true)) {
            return ['success' => false, 'message' => 'Je hebt de beloning voor deze taak al geclaimd.', 'reward' => ''];
        }

        return DB::transaction(function () use ($character, $taskKey) {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $perks = $locked->perks ?? [];
            $claimed = $perks['rookie_rewards_claimed'] ?? [];

            if (in_array($taskKey, $claimed, true)) {
                return ['success' => false, 'message' => 'Je hebt de beloning voor deze taak al geclaimd.', 'reward' => ''];
            }

            $rewardDesc = '';

            $levelService = app(LevelService::class);

            switch ($taskKey) {
                case 'first_theft':
                    $locked->cash += 1000;
                    $levelService->addXp($locked, 15);
                    $rewardDesc = '€1.000 contant en 15 XP';
                    break;

                case 'bank_deposit':
                    $locked->cash += 500;
                    $locked->respect = max(0, (int) ($locked->respect ?? 0) + 5);
                    $levelService->addXp($locked, 15);

                    // The task label promises 5 cigarettes, so put them in the inventory.
                    $cigarettes = Item::where('slug', 'sigaretten')->first();
                    if ($cigarettes) {
                        $inventoryRow = CharacterItem::firstOrCreate(
                            ['character_id' => $locked->id, 'item_id' => $cigarettes->id],
                            ['quantity' => 0]
                        );
                        $inventoryRow->increment('quantity', 5);
                    }

                    $rewardDesc = '€500 contant, 5 Sigaretten en 15 XP';
                    break;

                case 'training':
                    $levelService->addXp($locked, 15);
                    $maxStat = $locked->getMaxStatForLevel();
                    $locked->strength = min($maxStat, (int) $locked->strength + 1);
                    $locked->defense = min($maxStat, (int) $locked->defense + 1);
                    $rewardDesc = '+1 Kracht, +1 Verdediging en 15 XP';
                    break;

                case 'first_fight':
                    $locked->bullets = (int) ($locked->bullets ?? 0) + 30;
                    $levelService->addXp($locked, 25);
                    $rewardDesc = '30 Kogels en 25 XP';
                    break;

                case 'sponsor_vip':
                    $currentExpiry = $locked->vip_expires_at && $locked->vip_expires_at->isFuture()
                        ? $locked->vip_expires_at
                        : now();
                    $locked->is_vip = true;
                    $locked->vip_expires_at = $currentExpiry->addDay();
                    $levelService->addXp($locked, 25);
                    $rewardDesc = '1 Dag Gratis VIP status en 25 XP';
                    break;
            }

            $claimed[] = $taskKey;
            $perks['rookie_rewards_claimed'] = array_values(array_unique($claimed));

            // Check if all 5 are now claimed to award grand reward
            $grandUnlocked = false;
            $allKeys = array_keys(self::TASKS);
            $hasAll = count(array_diff($allKeys, $perks['rookie_rewards_claimed'])) === 0;

            if ($hasAll && empty($perks['rookie_grand_claimed'])) {
                $perks['rookie_grand_claimed'] = true;
                $locked->cash += 5000;
                $levelService->addXp($locked, 50);
                if (empty($locked->title) || $locked->title === 'Nieuwe Patiënt') {
                    $locked->title = 'Kliniek Overlever';
                }
                $grandUnlocked = true;
            }

            $locked->perks = $perks;
            $locked->save();
            $character->refresh();

            $msg = 'Gefeliciteerd! Je hebt '.$rewardDesc.' ontvangen.';
            if ($grandUnlocked) {
                $msg .= ' Je hebt álle 5 de Rookie overlevingstaken voltooid en ontvangt de Hoofdprijs: €5.000 contant, 50 XP én de titel Kliniek Overlever!';
            }

            return [
                'success' => true,
                'message' => $msg,
                'reward' => $rewardDesc,
                'grand_unlocked' => $grandUnlocked,
            ];
        });
    }

    /**
     * Mark the intake tour as completed and give starter bonus.
     *
     * @return array{success: bool, message: string}
     */
    public function completeTour(Character $character): array
    {
        $perks = $character->perks ?? [];

        if (! empty($perks['intake_tour_completed'])) {
            return [
                'success' => true,
                'message' => 'Rondleiding was al afgerond.',
            ];
        }

        $perks['intake_tour_completed'] = true;
        $character->cash += 1000;
        $character->perks = $perks;
        $character->save();

        return [
            'success' => true,
            'message' => 'Welkom in FPC De Balk! Je hebt de intake rondleiding afgerond en €1.000 startersbonus ontvangen.',
        ];
    }
}
