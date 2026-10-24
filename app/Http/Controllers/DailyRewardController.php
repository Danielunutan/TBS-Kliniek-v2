<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyRewardController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
    ) {}

    /**
     * Sectors on the Wheel of Fortune (No VIP stars - contains cash, masks, koffers, vault codes, energy).
     */
    protected array $wheelSectors = [
        0 => ['id' => 'vault_code_1', 'label' => '1x Kluiscode',  'short' => '1x Kluis',   'type' => 'vault_code', 'value' => 1,    'color' => '#1e293b', 'icon' => 'fa-key',       'emoji' => '🔑'],
        1 => ['id' => 'masks_2',       'label' => '2x Maskers',    'short' => '2x Masker',  'type' => 'masks',      'value' => 2,    'color' => '#4c1d95', 'icon' => 'fa-mask',      'emoji' => '🎭'],
        2 => ['id' => 'koffer_1',      'label' => '1x XXL Koffer', 'short' => '1x Koffer',  'type' => 'koffer',     'value' => 1,    'color' => '#065f46', 'icon' => 'fa-briefcase', 'emoji' => '💼'],
        3 => ['id' => 'cash_2500',     'label' => '€ 2.500 Cash',  'short' => '€ 2.500',    'type' => 'cash',       'value' => 2500, 'color' => '#0f766e', 'icon' => 'fa-coins',     'emoji' => '💶'],
        4 => ['id' => 'energy_50',     'label' => '+50 Energie',   'short' => '+50 Nrg',    'type' => 'energy',     'value' => 50,   'color' => '#1e3a8a', 'icon' => 'fa-bolt',      'emoji' => '⚡'],
        5 => ['id' => 'vault_code_2', 'label' => '2x Kluiscodes', 'short' => '2x Kluis',   'type' => 'vault_code', 'value' => 2,    'color' => '#312e81', 'icon' => 'fa-key',       'emoji' => '🔑'],
        6 => ['id' => 'masks_5',       'label' => '5x Maskers',    'short' => '5x Masker',  'type' => 'masks',      'value' => 5,    'color' => '#831843', 'icon' => 'fa-mask',      'emoji' => '🎭'],
        7 => ['id' => 'cash_10000',    'label' => '€ 10.000 Buit', 'short' => '€ 10.000',   'type' => 'cash',       'value' => 10000, 'color' => '#78350f', 'icon' => 'fa-sack-dollar', 'emoji' => '💰'],
    ];

    /**
     * 7-day Streak schedule.
     */
    protected array $streakSchedule = [
        1 => ['cash' => 1500,  'energy' => 0,   'masks' => 0, 'koffers' => 0, 'vault_codes' => 0, 'xp' => 15,  'label' => '€ 1.500 Cash + 15 XP'],
        2 => ['cash' => 2500,  'energy' => 25,  'masks' => 0, 'koffers' => 0, 'vault_codes' => 0, 'xp' => 25,  'label' => '€ 2.500 + 25 Energie'],
        3 => ['cash' => 4000,  'energy' => 0,   'masks' => 2, 'koffers' => 0, 'vault_codes' => 0, 'xp' => 40,  'label' => '€ 4.000 + 2x Maskers 🎭'],
        4 => ['cash' => 6000,  'energy' => 0,   'masks' => 0, 'koffers' => 1, 'vault_codes' => 0, 'xp' => 60,  'label' => '€ 6.000 + 1x XXL Koffer 💼'],
        5 => ['cash' => 8500,  'energy' => 50,  'masks' => 0, 'koffers' => 0, 'vault_codes' => 1, 'xp' => 85,  'label' => '€ 8.500 + 1x Kluiscode 🔑'],
        6 => ['cash' => 12000, 'energy' => 0,   'masks' => 4, 'koffers' => 1, 'vault_codes' => 0, 'xp' => 120, 'label' => '€ 12.000 + 4 Maskers 🎭 + 1 Koffer 💼'],
        7 => ['cash' => 25000, 'energy' => 100, 'masks' => 5, 'koffers' => 2, 'vault_codes' => 2, 'xp' => 250, 'label' => '🎁 Directeur Kluiskist (€25.000 + 2 Koffers + 2 Kluiscodes)!'],
    ];

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $availableSpins = $character->availableSpinsCount();
        $canSpin = $character->canDailySpin();
        $canClaimStreak = $character->canClaimStreak();
        $isLoginBonusActive = Character::isLoginBonusWindowActive();
        $hasClaimedStreakToday = $character->hasClaimedStreakToday();
        $secondsUntilLoginBonus = Character::secondsUntilNextLoginBonus();
        $secondsRemainingInLoginBonus = Character::secondsRemainingInLoginBonus();
        $nextStreakDay = $character->getNextStreakDay();
        $currentStreak = (int) $character->streak_days;

        $sectors = $this->wheelSectors;
        $schedule = $this->streakSchedule;

        return view('game.daily.index', compact(
            'character',
            'availableSpins',
            'canSpin',
            'canClaimStreak',
            'isLoginBonusActive',
            'hasClaimedStreakToday',
            'secondsUntilLoginBonus',
            'secondsRemainingInLoginBonus',
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

        // Randomly pick a winning sector (0 to 7)
        $winningIndex = random_int(0, count($this->wheelSectors) - 1);
        $prize = $this->wheelSectors[$winningIndex];
        $rewardDescription = $prize['label'];

        $spinSuccess = DB::transaction(function () use ($character, $prize): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked->canDailySpin()) {
                return false;
            }

            // Apply prize to locked character
            switch ($prize['type']) {
                case 'cash':
                    $locked->cash += $prize['value'];
                    break;
                case 'energy':
                    $locked->energy = min($locked->max_energy, $locked->energy + $prize['value']);
                    break;
                case 'masks':
                    $locked->masks = ($locked->masks ?? 0) + $prize['value'];
                    break;
                case 'koffer':
                    $locked->xxl_cases = ($locked->xxl_cases ?? 0) + $prize['value'];
                    break;
                case 'vault_code':
                    $locked->vault_tokens = ($locked->vault_tokens ?? 0) + $prize['value'];
                    break;
            }

            // Update spin counts
            $maxSpins = $locked->isVip() ? 2 : 1;
            if (! $locked->last_daily_spin_at || ! $locked->last_daily_spin_at->isToday()) {
                $locked->daily_spins_today = 1;
            } else {
                $locked->daily_spins_today = min($maxSpins, $locked->daily_spins_today + 1);
            }
            $locked->last_daily_spin_at = Carbon::now();
            $locked->save();

            // Sync original model instance
            $character->cash = $locked->cash;
            $character->energy = $locked->energy;
            $character->masks = $locked->masks;
            $character->xxl_cases = $locked->xxl_cases;
            $character->vault_tokens = $locked->vault_tokens;
            $character->daily_spins_today = $locked->daily_spins_today;
            $character->last_daily_spin_at = $locked->last_daily_spin_at;

            return true;
        });

        if (! $spinSuccess) {
            $msg = 'Je hebt vandaag al al je gratis draaien verbruikt! Kom morgen weer terug.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'daily_spin',
            'icon' => $prize['icon'],
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
                    'masks' => $character->masks ?? 0,
                    'vault_tokens' => $character->vault_tokens ?? 0,
                    'xxl_cases' => $character->xxl_cases ?? 0,
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

        if (! Character::isLoginBonusWindowActive() && ! app()->environment('testing')) {
            return back()->with('error', 'De dagelijkse inlog bonus kan alleen worden geclaimd tussen 22:00 en 22:30!');
        }

        $reward = null;
        $newStreak = 1;

        $streakSuccess = DB::transaction(function () use ($character, &$reward, &$newStreak): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked->canClaimStreak()) {
                return false;
            }

            $now = Carbon::now();
            $currentStreak = (int) $locked->streak_days;

            if ($locked->last_streak_claim_at) {
                $diffDays = (int) $locked->last_streak_claim_at->startOfDay()->diffInDays($now->startOfDay());
                if ($diffDays === 1) {
                    $newStreak = ($currentStreak % 7) + 1;
                } else {
                    $newStreak = 1;
                }
            } else {
                $newStreak = 1;
            }

            $reward = $this->streakSchedule[$newStreak] ?? $this->streakSchedule[1];

            if ($reward['cash'] > 0) {
                $locked->cash += $reward['cash'];
            }
            if ($reward['energy'] > 0) {
                $locked->energy = min($locked->max_energy, $locked->energy + $reward['energy']);
            }
            if (($reward['masks'] ?? 0) > 0) {
                $locked->masks = ($locked->masks ?? 0) + $reward['masks'];
            }
            if (($reward['koffers'] ?? 0) > 0) {
                $locked->xxl_cases = ($locked->xxl_cases ?? 0) + $reward['koffers'];
            }
            if (($reward['vault_codes'] ?? 0) > 0) {
                $locked->vault_tokens = ($locked->vault_tokens ?? 0) + $reward['vault_codes'];
            }
            if ($reward['xp'] > 0) {
                $this->level->addXp($locked, $reward['xp']);
            }

            $locked->streak_days = $newStreak;
            $locked->last_streak_claim_at = $now;
            $locked->save();

            // Sync original model instance
            $character->cash = $locked->cash;
            $character->energy = $locked->energy;
            $character->masks = $locked->masks;
            $character->xxl_cases = $locked->xxl_cases;
            $character->vault_tokens = $locked->vault_tokens;
            $character->streak_days = $locked->streak_days;
            $character->last_streak_claim_at = $locked->last_streak_claim_at;

            return true;
        });

        if (! $streakSuccess) {
            return back()->with('error', 'Je hebt je dagelijkse inlog bonus voor vandaag al geclaimd! Kom morgen tussen 22:00 en 22:30 terug.');
        }

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'daily_streak',
            'icon' => 'fa-calendar-check',
            'title' => "Dag {$newStreak} Login Streak Geclaimd!",
            'message' => "Je hebt je dagelijkse loginbeloning geclaimd: {$reward['label']}!",
            'is_read' => false,
        ]);

        return redirect()->route('daily.index')
            ->with('success', "Dag {$newStreak} login beloning succesvol geclaimd: {$reward['label']}!");
    }
}
