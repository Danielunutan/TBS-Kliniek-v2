<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BattlePassController extends Controller
{
    public const MAX_PASS_LEVEL = 150;

    /**
     * @var array<int, array{level: int, req_level: int, req_respect: int, tracks: array<string, array{title: string, icon: string, rewards: array<string, int>, summary: string}>}>|null
     */
    protected static ?array $cachedConfig = null;

    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Get the full configuration of the Season Battle Pass (150 Levels, 4 Tracks: FREE, TBS V.I.P. Lid, Gangster Elite V.I.P., Kliniek Baas V.I.P.).
     * Rewards feature scarce Stars, minimal Masks, Vault Codes (Codes), XP, Respect, Cash/Bank, and Strength (Kracht).
     *
     * @return array<int, array{level: int, req_level: int, req_respect: int, tracks: array<string, array{title: string, icon: string, rewards: array<string, int>, summary: string}>}>
     */
    public static function getPassConfig(): array
    {
        if (self::$cachedConfig !== null) {
            return self::$cachedConfig;
        }

        $levels = [];

        $freeTitles = [
            1 => ['Afdeling Startpakket', 'fa-bolt'],
            2 => ['Luchtplaats Krachttraining', 'fa-dumbbell'],
            3 => ['Kliniek Kluiscodes & XP', 'fa-key'],
            4 => ['Paviljoen Respect & Ervaring', 'fa-handshake-simple'],
            5 => ['Mijlpaal Kluispakket', 'fa-briefcase'],
            6 => ['Celdienst Zakgeld & XP', 'fa-sack-dollar'],
            7 => ['Halterbank Kracht Boost', 'fa-dumbbell'],
            8 => ['Bewaker Kluiscodes & Cash', 'fa-key'],
            9 => ['Afdeling Status & XP', 'fa-award'],
            0 => ['Grote Seizoens Mijlpaal', 'fa-trophy'],
        ];

        $vip1Titles = [
            1 => ['TBS V.I.P. Lid Startpakket', 'fa-star'],
            2 => ['TBS V.I.P. Lid Spierkracht', 'fa-dumbbell'],
            3 => ['TBS V.I.P. Lid Kluiscodes', 'fa-key'],
            4 => ['TBS V.I.P. Lid Dossier XP', 'fa-bolt'],
            5 => ['TBS V.I.P. Lid Mijlpaal Koffer', 'fa-briefcase'],
            6 => ['TBS V.I.P. Lid Witwas Storting', 'fa-building-columns'],
            7 => ['TBS V.I.P. Lid Zware Training', 'fa-dumbbell'],
            8 => ['TBS V.I.P. Lid Dubbele Codes', 'fa-unlock-keyhole'],
            9 => ['TBS V.I.P. Lid Paviljoen Aanzien', 'fa-handshake-simple'],
            0 => ['TBS V.I.P. Lid Hoofdmijlpaal', 'fa-crown'],
        ];

        $vip2Titles = [
            1 => ['Gangster Elite Kapitaal', 'fa-gem'],
            2 => ['Gangster Elite Kracht & Anabolen', 'fa-dumbbell'],
            3 => ['Gangster Elite Master Kluiscodes', 'fa-key'],
            4 => ['Gangster Elite Syndicaat XP', 'fa-fire-flame-curved'],
            5 => ['Gangster Elite Mijlpaal', 'fa-box-open'],
            6 => ['Gangster Elite Off-Shore Bank', 'fa-vault'],
            7 => ['Gangster Elite Boksring Dominant', 'fa-hand-fist'],
            8 => ['Gangster Elite Elektronische Codes', 'fa-microchip'],
            9 => ['Gangster Elite Onderwereld Respect', 'fa-award'],
            0 => ['Gangster Elite Hoofdkist', 'fa-gem'],
        ];

        $vip3Titles = [
            1 => ['Kliniek Baas Imperium', 'fa-crown'],
            2 => ['Kliniek Baas Titanen Kracht', 'fa-dumbbell'],
            3 => ['Kliniek Baas Directeur Kluiscodes', 'fa-key'],
            4 => ['Kliniek Baas Meesterbrein XP', 'fa-bolt-lightning'],
            5 => ['Kliniek Baas Syndicaat Kluiskist', 'fa-box-open'],
            6 => ['Kliniek Baas Zwarte Bankrekening', 'fa-sack-dollar'],
            7 => ['Kliniek Baas Onverslaanbare Kracht', 'fa-hand-fist'],
            8 => ['Kliniek Baas Hoofdkluis Codes', 'fa-fingerprint'],
            9 => ['Kliniek Baas Autoriteit', 'fa-dragon'],
            0 => ['Kliniek Baas Trofee', 'fa-trophy'],
        ];

        for ($lvl = 1; $lvl <= self::MAX_PASS_LEVEL; $lvl++) {
            $mod = $lvl % 10;
            $isMilestone5 = ($lvl % 5 === 0);
            $isMilestone10 = ($lvl % 10 === 0);
            $isMilestone25 = ($lvl % 25 === 0);
            $isFinale = ($lvl === self::MAX_PASS_LEVEL);

            // Smooth respect recommendation curve across 150 levels
            $reqRespect = $lvl === 1 ? 0 : (int) round(8 * ($lvl ** 1.85) / 25) * 25;

            // Base scaling values per level (1..150)
            $baseCash = 1200 + ($lvl * 1250) + ((int) floor($lvl / 15) * 4500);
            $baseXp = 50 + ($lvl * 30);
            $baseRespect = 5 + (int) floor($lvl * 1.2);
            $baseStrength = 2 + (int) floor($lvl / 4);

            // -------------------------
            // TRACK 1: FREE PASS
            // -------------------------
            $freeRewards = [];
            if ($isFinale) {
                $freeRewards = [
                    'cash' => 350000,
                    'xp' => 7500,
                    'strength' => 80,
                    'respect' => 300,
                    'vault_tokens' => 5,
                    'stars' => 10,
                    'masks' => 1,
                ];
            } elseif ($isMilestone25) {
                $freeRewards = [
                    'cash' => (int) round($baseCash * 2.2),
                    'xp' => (int) round($baseXp * 1.6),
                    'respect' => $baseRespect + 20,
                    'strength' => $baseStrength + 6,
                    'vault_tokens' => 2,
                    'stars' => 3,
                    'masks' => $lvl === 75 ? 1 : 0,
                ];
            } elseif ($isMilestone10) {
                $freeRewards = [
                    'cash' => (int) round($baseCash * 1.8),
                    'xp' => (int) round($baseXp * 1.4),
                    'respect' => $baseRespect + 12,
                    'strength' => $baseStrength + 4,
                    'vault_tokens' => 2,
                    'stars' => 2,
                ];
            } elseif ($isMilestone5) {
                $freeRewards = [
                    'cash' => (int) round($baseCash * 1.35),
                    'xp' => $baseXp,
                    'respect' => $baseRespect + 6,
                    'vault_tokens' => 1,
                    'stars' => 1,
                    'unopened_bags' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                $freeRewards = [
                    'cash' => $baseCash,
                    'strength' => $baseStrength,
                    'xp' => $baseXp,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                $freeRewards = [
                    'cash' => $baseCash,
                    'vault_tokens' => 1,
                    'respect' => $baseRespect,
                    'xp' => (int) round($baseXp * 0.85),
                ];
            } elseif (in_array($mod, [4, 9], true)) {
                $freeRewards = [
                    'cash' => $baseCash,
                    'respect' => $baseRespect + 5,
                    'strength' => max(2, (int) round($baseStrength * 0.85)),
                    'xp' => $baseXp,
                ];
            } else {
                $freeRewards = [
                    'cash' => $baseCash,
                    'xp' => $baseXp,
                    'respect' => $baseRespect,
                    'energy' => 30 + ($lvl % 4) * 10,
                ];
            }
            $freeRewards = array_filter($freeRewards, fn (int $v): bool => $v > 0);

            // -------------------------
            // TRACK 2: TBS V.I.P. LID (VIP 1)
            // -------------------------
            $vip1Cash = $baseCash * 2;
            $vip1Xp = (int) round($baseXp * 1.5);
            $vip1Respect = (int) round($baseRespect * 1.75);
            $vip1Strength = (int) round($baseStrength * 1.6);

            if ($isFinale) {
                $vip1Rewards = [
                    'cash' => 750000,
                    'xp' => 12000,
                    'strength' => 125,
                    'respect' => 500,
                    'vault_tokens' => 8,
                    'stars' => 20,
                    'masks' => 1,
                ];
            } elseif ($isMilestone25) {
                $vip1Rewards = [
                    'cash' => (int) round($vip1Cash * 1.8),
                    'xp' => $vip1Xp,
                    'strength' => $vip1Strength + 8,
                    'respect' => $vip1Respect + 25,
                    'vault_tokens' => 3,
                    'stars' => 5,
                    'masks' => 1,
                ];
            } elseif ($isMilestone10) {
                $vip1Rewards = [
                    'cash' => (int) round($vip1Cash * 1.5),
                    'xp' => $vip1Xp,
                    'strength' => $vip1Strength + 5,
                    'respect' => $vip1Respect + 15,
                    'vault_tokens' => 3,
                    'stars' => 3,
                ];
            } elseif ($isMilestone5) {
                $vip1Rewards = [
                    'cash' => (int) round($vip1Cash * 1.25),
                    'xp' => $vip1Xp,
                    'respect' => $vip1Respect,
                    'vault_tokens' => 2,
                    'stars' => 2,
                    'unopened_bags' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                $vip1Rewards = [
                    'cash' => $vip1Cash,
                    'strength' => $vip1Strength,
                    'xp' => $vip1Xp,
                    'stars' => 1,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                $vip1Rewards = [
                    'cash' => $vip1Cash,
                    'vault_tokens' => 2,
                    'xp' => $vip1Xp,
                    'respect' => $vip1Respect,
                    'stars' => 1,
                ];
            } else {
                $vip1Rewards = [
                    'cash' => $vip1Cash,
                    'xp' => $vip1Xp,
                    'respect' => $vip1Respect,
                    'strength' => max(3, (int) round($vip1Strength * 0.75)),
                    'stars' => 1,
                ];
            }
            $vip1Rewards = array_filter($vip1Rewards, fn (int $v): bool => $v > 0);

            // -------------------------
            // TRACK 3: GANGSTER ELITE V.I.P. (VIP 2)
            // -------------------------
            $vip2Cash = (int) round($baseCash * 3.6);
            $vip2Xp = (int) round($baseXp * 2.2);
            $vip2Respect = (int) round($baseRespect * 2.6);
            $vip2Strength = (int) round($baseStrength * 2.3);

            if ($isFinale) {
                $vip2Rewards = [
                    'cash' => 1500000,
                    'xp' => 18000,
                    'strength' => 190,
                    'respect' => 850,
                    'vault_tokens' => 12,
                    'stars' => 35,
                    'masks' => 2,
                    'xxl_cases' => 1,
                ];
            } elseif ($isMilestone25) {
                $vip2Rewards = [
                    'cash' => (int) round($vip2Cash * 1.75),
                    'xp' => $vip2Xp,
                    'strength' => $vip2Strength + 10,
                    'respect' => $vip2Respect + 35,
                    'vault_tokens' => 4,
                    'stars' => 8,
                    'masks' => 1,
                    'xxl_cases' => 1,
                ];
            } elseif ($isMilestone10) {
                $vip2Rewards = [
                    'cash' => (int) round($vip2Cash * 1.45),
                    'xp' => $vip2Xp,
                    'strength' => $vip2Strength + 7,
                    'respect' => $vip2Respect + 25,
                    'vault_tokens' => 3,
                    'stars' => 5,
                    'masks' => $lvl % 30 === 0 ? 1 : 0,
                ];
            } elseif ($isMilestone5) {
                $vip2Rewards = [
                    'cash' => (int) round($vip2Cash * 1.25),
                    'xp' => $vip2Xp,
                    'strength' => $vip2Strength,
                    'vault_tokens' => 2,
                    'stars' => 4,
                    'unopened_bags' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                $vip2Rewards = [
                    'cash' => $vip2Cash,
                    'strength' => $vip2Strength + 4,
                    'xp' => $vip2Xp,
                    'respect' => $vip2Respect,
                    'stars' => 2,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                $vip2Rewards = [
                    'cash' => $vip2Cash,
                    'vault_tokens' => 2,
                    'xp' => $vip2Xp,
                    'strength' => max(4, (int) round($vip2Strength * 0.8)),
                    'stars' => 2,
                ];
            } else {
                $vip2Rewards = [
                    'cash' => $vip2Cash,
                    'xp' => $vip2Xp,
                    'respect' => $vip2Respect,
                    'strength' => $vip2Strength,
                    'vault_tokens' => 1,
                    'stars' => 2,
                ];
            }
            $vip2Rewards = array_filter($vip2Rewards, fn (int $v): bool => $v > 0);

            // -------------------------
            // TRACK 4: KLINIEK BAAS V.I.P. (VIP 3)
            // -------------------------
            $vip3Cash = (int) round($baseCash * 6.5);
            $vip3Xp = (int) round($baseXp * 3.0);
            $vip3Respect = (int) round($baseRespect * 3.8);
            $vip3Strength = (int) round($baseStrength * 3.4);

            if ($isFinale) {
                $vip3Rewards = [
                    'cash' => 3000000,
                    'xp' => 30000,
                    'strength' => 300,
                    'respect' => 1500,
                    'vault_tokens' => 20,
                    'stars' => 60,
                    'masks' => 2,
                    'xxl_cases' => 2,
                ];
            } elseif ($isMilestone25) {
                $vip3Rewards = [
                    'cash' => (int) round($vip3Cash * 1.75),
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength + 15,
                    'respect' => $vip3Respect + 60,
                    'vault_tokens' => 6,
                    'stars' => 12,
                    'masks' => 1,
                    'xxl_cases' => 1,
                ];
            } elseif ($isMilestone10) {
                $vip3Rewards = [
                    'cash' => (int) round($vip3Cash * 1.45),
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength + 10,
                    'respect' => $vip3Respect + 40,
                    'vault_tokens' => 5,
                    'stars' => 8,
                    'masks' => 1,
                ];
            } elseif ($isMilestone5) {
                $vip3Rewards = [
                    'cash' => (int) round($vip3Cash * 1.25),
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength,
                    'respect' => $vip3Respect,
                    'vault_tokens' => 3,
                    'stars' => 6,
                    'unopened_bags' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                $vip3Rewards = [
                    'cash' => $vip3Cash,
                    'strength' => $vip3Strength + 6,
                    'xp' => $vip3Xp,
                    'respect' => $vip3Respect,
                    'vault_tokens' => 2,
                    'stars' => 3,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                $vip3Rewards = [
                    'cash' => $vip3Cash,
                    'vault_tokens' => 3,
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength,
                    'respect' => $vip3Respect,
                    'stars' => 3,
                ];
            } else {
                $vip3Rewards = [
                    'cash' => $vip3Cash,
                    'xp' => $vip3Xp,
                    'respect' => $vip3Respect,
                    'strength' => $vip3Strength,
                    'vault_tokens' => 1,
                    'stars' => 3 + ($lvl % 2),
                ];
            }
            $vip3Rewards = array_filter($vip3Rewards, fn (int $v): bool => $v > 0);

            $levels[$lvl] = [
                'level' => $lvl,
                'req_level' => $lvl,
                'req_respect' => $reqRespect,
                'tracks' => [
                    'free' => [
                        'title' => $isFinale ? 'Seizoen 1 Finale Dossier' : $freeTitles[$mod][0],
                        'icon' => $isFinale ? 'fa-trophy' : $freeTitles[$mod][1],
                        'rewards' => $freeRewards,
                        'summary' => self::formatSummary($freeRewards),
                    ],
                    'vip1' => [
                        'title' => $isFinale ? 'TBS V.I.P. Lid Kampioen' : $vip1Titles[$mod][0],
                        'icon' => $isFinale ? 'fa-crown' : $vip1Titles[$mod][1],
                        'rewards' => $vip1Rewards,
                        'summary' => self::formatSummary($vip1Rewards),
                    ],
                    'vip2' => [
                        'title' => $isFinale ? 'Gangster Elite V.I.P. Keizer' : $vip2Titles[$mod][0],
                        'icon' => $isFinale ? 'fa-gem' : $vip2Titles[$mod][1],
                        'rewards' => $vip2Rewards,
                        'summary' => self::formatSummary($vip2Rewards),
                    ],
                    'vip3' => [
                        'title' => $isFinale ? 'Kliniek Baas V.I.P. Legende' : $vip3Titles[$mod][0],
                        'icon' => $isFinale ? 'fa-dragon' : $vip3Titles[$mod][1],
                        'rewards' => $vip3Rewards,
                        'summary' => self::formatSummary($vip3Rewards),
                    ],
                ],
            ];
        }

        self::$cachedConfig = $levels;

        return self::$cachedConfig;
    }

    /**
     * Format a human-readable Dutch summary string from a rewards array.
     *
     * @param  array<string, int>  $rewards
     */
    protected static function formatSummary(array $rewards): string
    {
        $parts = [];
        $labels = [
            'cash' => fn (int $v): string => '€'.number_format($v, 0, ',', '.').' Cash',
            'bank' => fn (int $v): string => '€'.number_format($v, 0, ',', '.').' Bank',
            'xp' => fn (int $v): string => '+'.number_format($v, 0, ',', '.').' XP',
            'strength' => fn (int $v): string => '+'.number_format($v, 0, ',', '.').' Kracht',
            'respect' => fn (int $v): string => '+'.number_format($v, 0, ',', '.').' Respect',
            'vault_tokens' => fn (int $v): string => $v.' '.($v === 1 ? 'Code' : 'Codes'),
            'stars' => fn (int $v): string => $v.' '.($v === 1 ? 'Ster' : 'Sterren'),
            'masks' => fn (int $v): string => $v.' '.($v === 1 ? 'Masker' : 'Maskers'),
            'unopened_bags' => fn (int $v): string => $v.'x Koffer',
            'xxl_cases' => fn (int $v): string => $v.'x XXL Kist',
            'bullets' => fn (int $v): string => $v.' Kogels',
            'energy' => fn (int $v): string => '+'.$v.' Energie',
            'health' => fn (int $v): string => '+'.$v.' HP',
            'vip_points' => fn (int $v): string => '+'.$v.' VIP Pnt',
        ];

        foreach ($rewards as $key => $amount) {
            if ($amount > 0 && isset($labels[$key])) {
                $parts[] = $labels[$key]($amount);
            }
        }

        return implode(' + ', $parts);
    }

    /**
     * Display the Battle Pass page with all 150 levels and 4 tracks.
     */
    public function index(): View
    {
        /** @var Character $character */
        $character = Auth::user()->character;
        $this->energyService->refresh($character);

        $config = self::getPassConfig();
        $claims = $character->perks['battle_pass_claims'] ?? [];
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();
        $claimableCount = $character->getUnclaimedBattlePassCount();

        return view('game.battlepass.index', compact('character', 'config', 'claims', 'supporterTier', 'isVip', 'claimableCount'));
    }

    /**
     * Claim an individual Battle Pass reward.
     */
    public function claim(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'level' => 'required|integer|between:1,'.self::MAX_PASS_LEVEL,
            'track' => 'required|string|in:free,vip1,vip2,vip3',
        ]);

        /** @var Character $character */
        $character = Auth::user()->character;
        $lvl = (int) $validated['level'];
        $track = (string) $validated['track'];

        $config = self::getPassConfig();
        if (! isset($config[$lvl]['tracks'][$track])) {
            return $this->respondBp($request, false, 'Ongeldig Battle Pass niveau of spoor.');
        }

        $levelData = $config[$lvl];
        $trackData = $levelData['tracks'][$track];

        // Check level requirement
        if ($character->level < $levelData['req_level']) {
            return $this->respondBp($request, false, "Je moet minimaal Level {$levelData['req_level']} hebben bereikt om dit niveau te ontgrendelen.");
        }

        // Check VIP tier requirement
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        if ($track === 'vip1' && (! $isVip || $supporterTier < 1)) {
            return $this->respondBp($request, false, 'Voor deze pas heb je minimaal TBS V.I.P. Lid (VIP 1) status nodig.');
        }

        if ($track === 'vip2' && (! $isVip || $supporterTier < 2)) {
            return $this->respondBp($request, false, 'Voor deze pas heb je minimaal Gangster Elite V.I.P. (VIP 2) status nodig.');
        }

        if ($track === 'vip3' && (! $isVip || $supporterTier < 3)) {
            return $this->respondBp($request, false, 'Voor deze pas heb je minimaal Kliniek Baas V.I.P. (VIP 3) status nodig.');
        }

        $claimKey = "lvl_{$lvl}_{$track}";
        $claims = [];

        // Record the claim and grant rewards under a row lock so parallel requests cannot collect a reward twice.
        $alreadyClaimed = DB::transaction(function () use ($character, $claimKey, $trackData, &$claims): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            $perks = $locked->perks ?? [];
            $claims = $perks['battle_pass_claims'] ?? [];

            if (in_array($claimKey, $claims, true)) {
                return true;
            }

            // Record claim first, then grant rewards (including XP level-ups)
            $claims[] = $claimKey;
            $perks['battle_pass_claims'] = $claims;
            $locked->perks = $perks;

            $this->applyRewards($locked, $trackData['rewards']);
            $locked->save();

            return false;
        });

        if ($alreadyClaimed) {
            return $this->respondBp($request, false, 'Je hebt deze beloning al geclaimd.', 'info', $claims);
        }

        $character->refresh();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_pass_claimed',
            'icon' => $trackData['icon'],
            'title' => "Battle Pass Level {$lvl} Geclaimd!",
            'message' => "Je hebt {$trackData['summary']} ontvangen op je account!",
            'is_read' => false,
        ]);

        return $this->respondBp($request, true, "🎉 Battle Pass Level {$lvl} ({$trackData['title']}) geclaimd: {$trackData['summary']}!", 'success', $claims);
    }

    /**
     * Claim all available unlocked rewards in one click.
     */
    public function claimAll(Request $request): RedirectResponse|JsonResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;
        $config = self::getPassConfig();

        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        $claimedCount = 0;
        $aggregatedRewards = [];
        $claims = [];

        // Collect and grant all open rewards under a row lock so parallel requests cannot collect them twice.
        DB::transaction(function () use ($character, $config, $supporterTier, $isVip, &$claimedCount, &$aggregatedRewards, &$claims): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            $perks = $locked->perks ?? [];
            $claims = $perks['battle_pass_claims'] ?? [];
            $claimLookup = array_flip($claims);

            foreach ($config as $lvlNum => $data) {
                if ($locked->level < $data['req_level']) {
                    continue;
                }

                $tracksToCheck = ['free'];
                if ($isVip && $supporterTier >= 1) {
                    $tracksToCheck[] = 'vip1';
                }
                if ($isVip && $supporterTier >= 2) {
                    $tracksToCheck[] = 'vip2';
                }
                if ($isVip && $supporterTier >= 3) {
                    $tracksToCheck[] = 'vip3';
                }

                foreach ($tracksToCheck as $track) {
                    $claimKey = "lvl_{$lvlNum}_{$track}";
                    if (! isset($claimLookup[$claimKey])) {
                        $rewards = $data['tracks'][$track]['rewards'];
                        foreach ($rewards as $key => $amount) {
                            $aggregatedRewards[$key] = ($aggregatedRewards[$key] ?? 0) + $amount;
                        }

                        $claims[] = $claimKey;
                        $claimLookup[$claimKey] = true;
                        $claimedCount++;
                    }
                }
            }

            if ($claimedCount === 0) {
                return;
            }

            $perks['battle_pass_claims'] = $claims;
            $locked->perks = $perks;

            $this->applyRewards($locked, $aggregatedRewards);
            $locked->save();
        });

        if ($claimedCount === 0) {
            return $this->respondBp($request, false, 'Er zijn op dit moment geen ongeclaimde beloningen die voor jou ontgrendeld zijn.', 'info', $claims);
        }

        $character->refresh();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_pass_claimed',
            'icon' => 'fa-trophy',
            'title' => "{$claimedCount} Battle Pass Beloningen Geclaimd!",
            'message' => 'Alle ontgrendelde Battle Pass niveaus zijn direct bijgeschreven op je profiel!',
            'is_read' => false,
        ]);

        return $this->respondBp($request, true, "🎉 Gefeliciteerd! Je hebt in één klik {$claimedCount} Battle Pass beloningen geclaimd (".self::formatSummary($aggregatedRewards).')!', 'success', $claims);
    }

    /**
     * Return instant JSON for Alpine fetch or standard RedirectResponse fallback.
     *
     * @param  array<int, string>  $claims
     */
    protected function respondBp(Request $request, bool $success, string $message, string $type = 'error', array $claims = []): RedirectResponse|JsonResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character->fresh();

        if ($request->hasHeader('X-BP-Fast')) {
            return response()->json([
                'success' => $success,
                'type' => $type,
                'message' => $message,
                'claims' => array_values($claims ?: ($character->perks['battle_pass_claims'] ?? [])),
                'claimableCount' => $character->getUnclaimedBattlePassCount(),
                'character' => [
                    'level' => $character->level,
                    'xp' => $character->xp,
                    'cash' => $character->cash,
                    'bank' => $character->bank,
                    'respect' => $character->respect,
                    'strength' => $character->strength,
                    'stars' => $character->stars,
                    'vault_tokens' => $character->vault_tokens,
                    'masks' => $character->masks,
                    'energy' => $character->energy,
                    'max_energy' => $character->max_energy,
                    'health' => $character->health,
                    'max_health' => $character->max_health,
                ],
            ]);
        }

        return back()->with($type, $message);
    }

    /**
     * Helper to apply rewards array to character.
     *
     * @param  array<string, int>  $rewards
     */
    protected function applyRewards(Character $character, array $rewards): void
    {
        if (isset($rewards['cash'])) {
            $character->cash = ($character->cash ?? 0) + $rewards['cash'];
        }
        if (isset($rewards['bank'])) {
            $character->bank = ($character->bank ?? 0) + $rewards['bank'];
        }
        if (isset($rewards['stars'])) {
            $character->stars = ($character->stars ?? 0) + $rewards['stars'];
        }
        if (isset($rewards['vip_points'])) {
            $character->vip_points = ($character->vip_points ?? 0) + $rewards['vip_points'];
        }
        if (isset($rewards['respect'])) {
            $character->respect = ($character->respect ?? 0) + $rewards['respect'];
        }
        // Apply XP first so any level-up raises getMaxStatForLevel() before strength is clamped.
        if (isset($rewards['xp']) && $rewards['xp'] > 0) {
            $this->levelService->addXp($character, $rewards['xp']);
        }
        if (isset($rewards['strength'])) {
            $character->strength = min($character->getMaxStatForLevel(), ($character->strength ?? 0) + $rewards['strength']);
        }
        if (isset($rewards['bullets'])) {
            $character->bullets = ($character->bullets ?? 0) + $rewards['bullets'];
        }
        if (isset($rewards['unopened_bags'])) {
            $character->unopened_bags = ($character->unopened_bags ?? 0) + $rewards['unopened_bags'];
        }
        if (isset($rewards['xxl_cases'])) {
            $character->xxl_cases = ($character->xxl_cases ?? 0) + $rewards['xxl_cases'];
        }
        if (isset($rewards['masks'])) {
            $character->masks = ($character->masks ?? 0) + $rewards['masks'];
        }
        if (isset($rewards['vault_tokens'])) {
            $character->vault_tokens = ($character->vault_tokens ?? 0) + $rewards['vault_tokens'];
        }
        if (isset($rewards['energy'])) {
            $character->energy = min($character->max_energy, ($character->energy ?? 0) + $rewards['energy']);
        }
        if (isset($rewards['health'])) {
            $character->health = min($character->max_health, ($character->health ?? 0) + $rewards['health']);
        }
    }
}
