<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyRewardController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
    ) {}

    /**
     * Sectors on the Wheel of Fortune.
     */
    protected array $wheelSectors = [
        0 => ['id' => 'cash_2500',  'label' => '€ 2.500 Contant',  'type' => 'cash',    'value' => 2500,  'color' => '#8b5cf6', 'icon' => 'fa-coins'],
        1 => ['id' => 'energy_50',   'label' => '+50 Energie',       'type' => 'energy',  'value' => 50,    'color' => '#10b981', 'icon' => 'fa-bolt'],
        2 => ['id' => 'stars_5',     'label' => '5 VIP Sterren',     'type' => 'stars',   'value' => 5,     'color' => '#f59e0b', 'icon' => 'fa-star'],
        3 => ['id' => 'respect_25',  'label' => '+25 Respect',       'type' => 'respect', 'value' => 25,    'color' => '#ec4899', 'icon' => 'fa-fire-flame-curved'],
        4 => ['id' => 'cash_10000', 'label' => '€ 10.000 Jackpot',  'type' => 'cash',    'value' => 10000, 'color' => '#6366f1', 'icon' => 'fa-sack-dollar'],
        5 => ['id' => 'health_100',  'label' => '+100 HP Gezondheid', 'type' => 'health',  'value' => 100,   'color' => '#ef4444', 'icon' => 'fa-heart-pulse'],
        6 => ['id' => 'stars_15',    'label' => '15 VIP Sterren',    'type' => 'stars',   'value' => 15,    'color' => '#d97706', 'icon' => 'fa-crown'],
        7 => ['id' => 'cash_5000',   'label' => '€ 5.000 Buit',      'type' => 'cash',    'value' => 5000,  'color' => '#06b6d4', 'icon' => 'fa-gem'],
    ];

    /**
     * 7-day Streak schedule.
     */
    protected array $streakSchedule = [
        1 => ['cash' => 1500,  'energy' => 0,  'respect' => 0,  'stars' => 0,  'xp' => 15,  'label' => '€1.500 Cash + 15 XP'],
        2 => ['cash' => 2500,  'energy' => 25, 'respect' => 0,  'stars' => 0,  'xp' => 25,  'label' => '€2.500 + 25 Energie'],
        3 => ['cash' => 4000,  'energy' => 0,  'respect' => 15, 'stars' => 0,  'xp' => 40,  'label' => '€4.000 + 15 Respect'],
        4 => ['cash' => 6000,  'energy' => 0,  'respect' => 0,  'stars' => 5,  'xp' => 60,  'label' => '€6.000 + 5 Sterren'],
        5 => ['cash' => 8500,  'energy' => 50, 'respect' => 0,  'stars' => 0,  'xp' => 85,  'label' => '€8.500 + 50 Energie'],
        6 => ['cash' => 12000, 'energy' => 0,  'respect' => 35, 'stars' => 0,  'xp' => 120, 'label' => '€12.000 + 35 Respect'],
        7 => ['cash' => 25000, 'energy' => 100, 'respect' => 50, 'stars' => 20, 'xp' => 250, 'label' => '🎁 Directeur Kluiskist!'],
    ];

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $availableSpins = $character->availableSpinsCount();
        $canSpin = $character->canDailySpin();
        $canClaimStreak = $character->canClaimStreak();

        // Calculate current active streak day for display
        $currentStreak = (int) $character->streak_days;
        if ($character->last_streak_claim_at) {
            $diffDays = $character->last_streak_claim_at->startOfDay()->diffInDays(now()->startOfDay());
            if ($diffDays > 1) {
                // Streak was broken
                $nextStreakDay = 1;
            } elseif ($diffDays === 1) {
                // Streak continued yesterday, today is next
                $nextStreakDay = ($currentStreak % 7) + 1;
            } else {
                // Already claimed today
                $nextStreakDay = max(1, $currentStreak % 7);
            }
        } else {
            $nextStreakDay = 1;
        }

        $sectors = $this->wheelSectors;
        $schedule = $this->streakSchedule;

        return view('game.daily.index', compact(
            'character',
            'availableSpins',
            'canSpin',
            'canClaimStreak',
            'currentStreak',
            'nextStreakDay',
            'sectors',
            'schedule'
        ));
    }

    /**
     * Spin the Wheel of Fortune (AJAX real-time or Form).
     */
    public function spin(Request $request): JsonResponse|RedirectResponse
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        if (! $character->canDailySpin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt vandaag al al je gratis draaien verbruikt! Kom morgen weer terug.',
                ], 422);
            }

            return back()->with('error', 'Je hebt vandaag al je gratis draaien verbruikt!');
        }

        // Randomly pick a winning sector (0 to 7)
        $winningIndex = random_int(0, count($this->wheelSectors) - 1);
        $prize = $this->wheelSectors[$winningIndex];

        // Apply prize to character
        $rewardDescription = $prize['label'];
        switch ($prize['type']) {
            case 'cash':
                $this->economy->addCash($character, $prize['value']);
                break;
            case 'energy':
                $character->energy = min($character->max_energy, $character->energy + $prize['value']);
                break;
            case 'stars':
                $character->stars = ($character->stars ?? 0) + $prize['value'];
                break;
            case 'respect':
                $character->respect = ($character->respect ?? 0) + $prize['value'];
                break;
            case 'health':
                $character->health = min($character->max_health, $character->health + $prize['value']);
                break;
        }

        // Update spin counts
        $maxSpins = $character->isVip() ? 2 : 1;
        if (! $character->last_daily_spin_at || ! $character->last_daily_spin_at->isToday()) {
            $character->daily_spins_today = 1;
        } else {
            $character->daily_spins_today = min($maxSpins, $character->daily_spins_today + 1);
        }
        $character->last_daily_spin_at = Carbon::now();
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'daily_spin',
            'title' => 'Rad van Fortuin gedraaid!',
            'message' => "Je draaide aan het Rad van Fortuin en won: {$rewardDescription}!",
            'is_read' => false,
        ]);

        $availableSpins = $character->availableSpinsCount();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'winning_index' => $winningIndex,
                'prize' => $prize,
                'message' => "Gefeliciteerd! Je hebt {$rewardDescription} gewonnen!",
                'available_spins' => $availableSpins,
                'character' => [
                    'cash' => $character->cash,
                    'energy' => $character->energy,
                    'health' => $character->health,
                    'stars' => $character->stars,
                    'respect' => $character->respect,
                ],
            ]);
        }

        return redirect()->route('daily.index')->with('success', "Gefeliciteerd! Je won: {$rewardDescription}!");
    }

    /**
     * Claim daily login streak.
     */
    public function claimStreak(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if (! $character->canClaimStreak()) {
            return back()->with('error', 'Je hebt je dagelijkse streak beloning voor vandaag al geclaimd!');
        }

        $now = Carbon::now();
        $currentStreak = (int) $character->streak_days;

        if ($character->last_streak_claim_at) {
            $diffDays = $character->last_streak_claim_at->startOfDay()->diffInDays($now->startOfDay());
            if ($diffDays === 1) {
                // Continued yesterday
                $newStreak = ($currentStreak % 7) + 1;
            } else {
                // Streak broken
                $newStreak = 1;
            }
        } else {
            $newStreak = 1;
        }

        $reward = $this->streakSchedule[$newStreak] ?? $this->streakSchedule[1];

        // Award rewards
        if ($reward['cash'] > 0) {
            $this->economy->addCash($character, $reward['cash']);
        }
        if ($reward['energy'] > 0) {
            $character->energy = min($character->max_energy, $character->energy + $reward['energy']);
        }
        if ($reward['respect'] > 0) {
            $character->respect = ($character->respect ?? 0) + $reward['respect'];
        }
        if ($reward['stars'] > 0) {
            $character->stars = ($character->stars ?? 0) + $reward['stars'];
        }
        if ($reward['xp'] > 0) {
            $this->level->addXp($character, $reward['xp']);
        }

        $character->streak_days = $newStreak;
        $character->last_streak_claim_at = $now;
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'daily_streak',
            'title' => "Dag {$newStreak} Login Streak Geclaimd!",
            'message' => "Je hebt je dagelijkse loginbeloning geclaimd: {$reward['label']}!",
            'is_read' => false,
        ]);

        return redirect()->route('daily.index')
            ->with('success', "Dag {$newStreak} login beloning succesvol geclaimd: {$reward['label']}!");
    }
}
