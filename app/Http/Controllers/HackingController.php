<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\HackingLog;
use App\Models\Item;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HackingController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Sectors on the Hacker Spinner wheel (10 distinct options).
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $wheelSectors = [
        0 => ['id' => 'cash_25k',      'label' => '€ 25.000 Cash',       'short' => '€ 25K',      'type' => 'cash',        'value' => 25000, 'color' => '#059669', 'icon' => 'fa-money-bill-wave', 'emoji' => '💶'],
        1 => ['id' => 'koffer_1',      'label' => '1x Smokkelkoffer',    'short' => '1x Koffer',  'type' => 'koffer',      'value' => 1,     'color' => '#4f46e5', 'icon' => 'fa-briefcase',       'emoji' => '💼'],
        2 => ['id' => 'kluiscode_1',   'label' => '1x Kluiscode',        'short' => '1x Code',    'type' => 'vault_code',  'value' => 1,     'color' => '#d97706', 'icon' => 'fa-key',             'emoji' => '🗝️'],
        3 => ['id' => 'xxl_koffer_1',  'label' => '1x XXL Koffer',       'short' => '1x XXL',     'type' => 'xxl_koffer',  'value' => 1,     'color' => '#7c3aed', 'icon' => 'fa-box-archive',     'emoji' => '🧳'],
        4 => ['id' => 'stars_5',       'label' => '5x VIP Sterren',      'short' => '5x Sterren', 'type' => 'stars',       'value' => 5,     'color' => '#eab308', 'icon' => 'fa-star',            'emoji' => '⭐'],
        5 => ['id' => 'cash_75k',      'label' => '€ 75.000 Mega Buit',  'short' => '€ 75K',      'type' => 'cash',        'value' => 75000, 'color' => '#10b981', 'icon' => 'fa-sack-dollar',     'emoji' => '💰'],
        6 => ['id' => 'sigaretten_15', 'label' => '15x Sigaretten',      'short' => '15x Peuk',   'type' => 'item',        'slug' => 'sigaretten', 'value' => 15, 'color' => '#ea580c', 'icon' => 'fa-smoking', 'emoji' => '🚬'],
        7 => ['id' => 'masks_4',       'label' => '4x Heist Maskers',    'short' => '4x Masker',  'type' => 'masks',       'value' => 4,     'color' => '#be123c', 'icon' => 'fa-mask',            'emoji' => '🎭'],
        8 => ['id' => 'telefoon_3',    'label' => '3x Smokkel Telefoons', 'short' => '3x Mobiel',  'type' => 'item',        'slug' => 'telefoon',   'value' => 3,  'color' => '#2563eb', 'icon' => 'fa-mobile-screen', 'emoji' => '📱'],
        9 => ['id' => 'ritalin_10',    'label' => '10x Ritalin Strips',  'short' => '10x Pillen', 'type' => 'item',        'slug' => 'ritalin',    'value' => 10, 'color' => '#0891b2', 'icon' => 'fa-pills',         'emoji' => '💊'],
    ];

    /**
     * Show the Cyber Hacking Terminal & Minigame.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energyService->refresh($character);

        $canHack = $character->canDailyHack();

        // Determine current session stage (1: Puzzle 1, 2: Puzzle 2, 3: Puzzle 3, 'wheel': Spin Wheel, 'done': Completed)
        $sessionDate = session('hacking_session_date');
        $today = now()->toDateString();

        if ($sessionDate !== $today) {
            session([
                'hacking_session_date' => $today,
                'hacking_stage' => 1,
            ]);
        }

        $currentStage = session('hacking_stage', 1);
        if (! $canHack) {
            $currentStage = 'done';
        }

        $wheelSectors = $this->wheelSectors;

        // Recent hacking logs
        $recentLogs = HackingLog::with('character')
            ->latest()
            ->take(8)
            ->get();

        // Seconds until midnight for countdown if already completed today
        $now = Carbon::now(config('app.timezone', 'Europe/Amsterdam'));
        $secondsUntilMidnight = $now->copy()->endOfDay()->diffInSeconds($now);

        return view('game.hacking.index', compact(
            'character',
            'canHack',
            'currentStage',
            'wheelSectors',
            'recentLogs',
            'secondsUntilMidnight'
        ));
    }

    /**
     * Advance puzzle progress when player successfully solves a terminal puzzle.
     */
    public function solvePuzzle(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je geen terminals hacken!',
            ], 403);
        }

        if (! $character->canDailyHack()) {
            return response()->json([
                'success' => false,
                'message' => 'Je hebt vandaag je dagelijkse terminal hack sessie al voltooid!',
            ], 400);
        }

        $puzzleNumber = (int) $request->input('puzzle', 1);
        $currentStage = (int) session('hacking_stage', 1);

        if ($puzzleNumber !== $currentStage) {
            return response()->json([
                'success' => false,
                'message' => "Ongeldige puzzel volgorde! Je bent momenteel bij Puzzel {$currentStage}.",
            ], 400);
        }

        if ($puzzleNumber === 1) {
            session(['hacking_stage' => 2]);

            return response()->json([
                'success' => true,
                'next_stage' => 2,
                'title' => 'FIREWALL BYPASS GESLAAGD!',
                'message' => 'Node reeks correct gevalideerd! Door naar Puzzel 2: Mainframe Decryptie.',
            ]);
        }

        if ($puzzleNumber === 2) {
            session(['hacking_stage' => 3]);

            return response()->json([
                'success' => true,
                'next_stage' => 3,
                'title' => 'CYPHER DECRYPTIE COMPLEET!',
                'message' => 'Server poorten gekoppeld! Door naar Puzzel 3: Hoofdserver Root Access.',
            ]);
        }

        if ($puzzleNumber === 3) {
            session(['hacking_stage' => 'wheel']);

            return response()->json([
                'success' => true,
                'next_stage' => 'wheel',
                'title' => 'ROOT ACCESS OVERRIDE SUCCESVOL!',
                'message' => 'Gefeliciteerd! Alle 3 de puzzels zijn opgelost. Draai nu aan de Hacker Spinner voor je beloning!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Onbekende puzzel status.',
        ], 400);
    }

    /**
     * Spin the Hacker Spinner reward wheel and claim rewards.
     */
    public function spin(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je niet draaien aan het beloningsrad!',
            ], 403);
        }

        if (! $character->canDailyHack()) {
            return response()->json([
                'success' => false,
                'message' => 'Je hebt je dagelijkse hack reward voor vandaag al geclaimd!',
            ], 400);
        }

        $currentStage = session('hacking_stage');
        if ($currentStage !== 'wheel') {
            return response()->json([
                'success' => false,
                'message' => 'Los eerst alle 3 de terminal puzzels op om de Hacker Spinner te ontgrendelen!',
            ], 400);
        }

        // Random roll for one of the 10 sectors (indices 0 to 9)
        $sectorIndex = mt_rand(0, 9);
        $sector = $this->wheelSectors[$sectorIndex];

        // Award main reward
        $rewardLabel = $sector['label'];

        match ($sector['type']) {
            'cash' => $character->cash += $sector['value'],
            'koffer' => $character->unopened_bags = ($character->unopened_bags ?? 0) + $sector['value'],
            'xxl_koffer' => $character->xxl_cases = ($character->xxl_cases ?? 0) + $sector['value'],
            'stars' => $character->vip_points = ($character->vip_points ?? 0) + $sector['value'],
            'masks' => $character->masks = ($character->masks ?? 0) + $sector['value'],
            'vault_code' => VaultCode::create([
                'character_id' => $character->id,
                'code' => strtoupper(substr(md5(uniqid()), 0, 8)),
                'is_used' => false,
            ]),
            'item' => $this->awardItem($character->id, $sector['slug'], $sector['value']),
            default => null,
        };

        // Check VIP extra bonus
        $isVip = $character->isVip();
        $vipBonusApplied = false;
        $vipBonusLabel = null;

        if ($isVip) {
            $vipBonusApplied = true;
            // VIPs receive additional cash + stars + XXL koffer + masks on top of what they get
            $bonusCash = 25000;
            $bonusStars = 3;
            $bonusCases = 1;
            $bonusMasks = 4;

            $character->cash += $bonusCash;
            $character->vip_points = ($character->vip_points ?? 0) + $bonusStars;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + $bonusCases;
            $character->masks = ($character->masks ?? 0) + $bonusMasks;

            $vipBonusLabel = '👑 VIP BONUS: +€25.000 Cash, +3 Sterren ⭐, +1 XXL Koffer 🧳 & +4 Maskers 🎭!';
        }

        // Mark completed today
        $character->last_hack_at = now();
        $character->hacks_completed_total = ($character->hacks_completed_total ?? 0) + 1;
        $character->save();

        session(['hacking_stage' => 'done']);

        // Record Log
        HackingLog::create([
            'character_id' => $character->id,
            'reward_id' => $sector['id'],
            'reward_label' => $rewardLabel,
            'vip_bonus_applied' => $vipBonusApplied,
            'vip_bonus_label' => $vipBonusLabel,
        ]);

        return response()->json([
            'success' => true,
            'sector_index' => $sectorIndex,
            'reward' => $sector,
            'is_vip' => $isVip,
            'vip_bonus_applied' => $vipBonusApplied,
            'vip_bonus_label' => $vipBonusLabel,
            'message' => "Gefeliciteerd! Je hebt '{$rewardLabel}' gewonnen!".($vipBonusApplied ? " {$vipBonusLabel}" : ''),
        ]);
    }

    /**
     * Award an inventory item to character.
     */
    protected function awardItem(int $characterId, string $slug, int $amount): void
    {
        $item = Item::where('slug', $slug)->first();
        if (! $item) {
            return;
        }

        $charItem = CharacterItem::firstOrCreate(
            ['character_id' => $characterId, 'item_id' => $item->id],
            ['quantity' => 0]
        );

        $charItem->quantity += $amount;
        $charItem->save();
    }
}
