<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BattlePassController extends Controller
{
    public const MAX_PASS_LEVEL = 60;

    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Get the full configuration of the 3-Month Season Battle Pass (60 Levels, 4 Tracks: FREE, VIP 1, VIP 2, VIP 3).
     * Rewards feature scarce Stars, minimal Masks, Vault Codes (Kluiscodex), XP, Respect, Cash/Bank, and Strength (Kracht).
     *
     * @return array<int, array{level: int, req_level: int, req_respect: int, tracks: array<string, array{title: string, icon: string, rewards: array<string, int>, summary: string}>}>
     */
    public static function getPassConfig(): array
    {
        $levels = [];

        $freeTitles = [
            1 => ['Afdeling Startpakket', 'fa-bolt'],
            2 => ['Luchtplaats Krachttraining', 'fa-dumbbell'],
            3 => ['Kliniek Kluiscodex & XP', 'fa-key'],
            4 => ['Paviljoen Respect & Ervaring', 'fa-handshake-simple'],
            5 => ['Mijlpaal Kluispakket', 'fa-briefcase'],
            6 => ['Celdienst Zakgeld & XP', 'fa-sack-dollar'],
            7 => ['Halterbank Kracht Boost', 'fa-dumbbell'],
            8 => ['Bewaker Kluiscodex & Cash', 'fa-key'],
            9 => ['Afdeling Status & XP', 'fa-award'],
            0 => ['Grote Seizoens Mijlpaal', 'fa-trophy'],
        ];

        $vip1Titles = [
            1 => ['VIP 1 Smokkel Start', 'fa-star'],
            2 => ['VIP 1 Spierkracht & Cash', 'fa-dumbbell'],
            3 => ['VIP 1 Kluiscodex Decoder', 'fa-key'],
            4 => ['VIP 1 Dossier XP & Respect', 'fa-bolt'],
            5 => ['VIP 1 Mijlpaal Koffer', 'fa-briefcase'],
            6 => ['VIP 1 Witwas Storting', 'fa-building-columns'],
            7 => ['VIP 1 Zware Training', 'fa-dumbbell'],
            8 => ['VIP 1 Dubbele Kluiscodex', 'fa-unlock-keyhole'],
            9 => ['VIP 1 Paviljoen Aanzien', 'fa-handshake-simple'],
            0 => ['VIP 1 Hoofdmijlpaal Pakket', 'fa-crown'],
        ];

        $vip2Titles = [
            1 => ['VIP 2 Elite Kapitaal', 'fa-gem'],
            2 => ['VIP 2 Kracht & Anabolen', 'fa-dumbbell'],
            3 => ['VIP 2 Master Kluiscodex', 'fa-key'],
            4 => ['VIP 2 Syndicaat XP & Status', 'fa-fire-flame-curved'],
            5 => ['VIP 2 Elite Mijlpaal', 'fa-box-open'],
            6 => ['VIP 2 Off-Shore Bank', 'fa-vault'],
            7 => ['VIP 2 Boksring Dominant', 'fa-hand-fist'],
            8 => ['VIP 2 Elektronische Codex', 'fa-microchip'],
            9 => ['VIP 2 Onderwereld Respect', 'fa-award'],
            0 => ['VIP 2 Gangster Elite Kist', 'fa-gem'],
        ];

        $vip3Titles = [
            1 => ['VIP 3 Baas Imperium', 'fa-crown'],
            2 => ['VIP 3 Titanen Kracht', 'fa-dumbbell'],
            3 => ['VIP 3 Directeur Kluiscodex', 'fa-key'],
            4 => ['VIP 3 Meesterbrein XP', 'fa-bolt-lightning'],
            5 => ['VIP 3 Syndicaat Kluiskist', 'fa-box-open'],
            6 => ['VIP 3 Zwarte Bankrekening', 'fa-sack-dollar'],
            7 => ['VIP 3 Onverslaanbare Kracht', 'fa-hand-fist'],
            8 => ['VIP 3 Hoofdkluis Codex', 'fa-fingerprint'],
            9 => ['VIP 3 Kliniek Autoriteit', 'fa-dragon'],
            0 => ['VIP 3 Kliniek Baas Trofee', 'fa-trophy'],
        ];

        for ($lvl = 1; $lvl <= self::MAX_PASS_LEVEL; $lvl++) {
            $mod = $lvl % 10;
            $isMilestone5 = ($lvl % 5 === 0);
            $isMilestone10 = ($lvl % 10 === 0);
            $isFinale = ($lvl === self::MAX_PASS_LEVEL);

            // Smooth 3-month respect recommendation curve (0 up to ~45.000 at level 60)
            $reqRespect = $lvl === 1 ? 0 : (int) round(12 * ($lvl ** 2) / 25) * 25;

            // Base scaling values per level
            $baseCash = 1500 + ($lvl * 1500) + ((int) floor($lvl / 10) * 5000);
            $baseXp = 60 + ($lvl * 35);
            $baseRespect = 5 + (int) floor($lvl * 1.5);
            $baseStrength = 2 + (int) floor($lvl / 3);

            // -------------------------
            // TRACK 1: FREE PASS
            // -------------------------
            $freeRewards = [];
            if ($isFinale) {
                $freeRewards = [
                    'cash' => 150000,
                    'xp' => 3500,
                    'strength' => 40,
                    'respect' => 150,
                    'vault_tokens' => 4,
                    'stars' => 5,
                    'masks' => 1,
                ];
            } elseif ($isMilestone10) {
                $freeRewards = [
                    'cash' => $baseCash * 2,
                    'xp' => (int) round($baseXp * 1.5),
                    'respect' => $baseRespect + 15,
                    'strength' => $baseStrength + 4,
                    'vault_tokens' => 2,
                    'stars' => 2,
                ];
            } elseif ($isMilestone5) {
                $freeRewards = [
                    'cash' => (int) round($baseCash * 1.4),
                    'xp' => $baseXp,
                    'respect' => $baseRespect + 8,
                    'vault_tokens' => 1,
                    'stars' => 1,
                    'unopened_bags' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                // Kracht + Cash + XP focus
                $freeRewards = [
                    'cash' => $baseCash,
                    'strength' => $baseStrength,
                    'xp' => $baseXp,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                // Kluiscodex + Respect + XP focus
                $freeRewards = [
                    'cash' => $baseCash,
                    'vault_tokens' => 1,
                    'respect' => $baseRespect,
                    'xp' => (int) round($baseXp * 0.8),
                ];
            } elseif (in_array($mod, [4, 9], true)) {
                // Respect + Kracht + XP focus
                $freeRewards = [
                    'cash' => $baseCash,
                    'respect' => $baseRespect + 5,
                    'strength' => max(2, (int) round($baseStrength * 0.8)),
                    'xp' => $baseXp,
                ];
            } else {
                // Standard Level (1, 6): Cash + XP + Energy/Respect
                $freeRewards = [
                    'cash' => $baseCash,
                    'xp' => $baseXp,
                    'respect' => $baseRespect,
                    'energy' => 30 + ($lvl % 4) * 10,
                ];
            }

            // -------------------------
            // TRACK 2: VIP 1 PASS
            // -------------------------
            $vip1Cash = $baseCash * 2;
            $vip1Xp = (int) round($baseXp * 1.5);
            $vip1Respect = (int) round($baseRespect * 1.8);
            $vip1Strength = (int) round($baseStrength * 1.6);

            if ($isFinale) {
                $vip1Rewards = [
                    'cash' => 350000,
                    'xp' => 5500,
                    'strength' => 65,
                    'respect' => 250,
                    'vault_tokens' => 6,
                    'stars' => 12,
                    'masks' => 1,
                ];
            } elseif ($isMilestone10) {
                $vip1Rewards = [
                    'cash' => (int) round($vip1Cash * 1.6),
                    'xp' => $vip1Xp,
                    'strength' => $vip1Strength + 5,
                    'respect' => $vip1Respect + 20,
                    'vault_tokens' => 3,
                    'stars' => 4,
                    'masks' => $lvl % 20 === 0 ? 1 : 0,
                ];
            } elseif ($isMilestone5) {
                $vip1Rewards = [
                    'cash' => (int) round($vip1Cash * 1.3),
                    'xp' => $vip1Xp,
                    'respect' => $vip1Respect,
                    'vault_tokens' => 2,
                    'stars' => 3,
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
                    'stars' => 2,
                ];
            } else {
                $vip1Rewards = [
                    'cash' => $vip1Cash,
                    'xp' => $vip1Xp,
                    'respect' => $vip1Respect,
                    'strength' => max(3, (int) round($vip1Strength * 0.75)),
                    'stars' => 1 + ($lvl % 2),
                ];
            }
            $vip1Rewards = array_filter($vip1Rewards, fn (int $v): bool => $v > 0);

            // -------------------------
            // TRACK 3: VIP 2 PASS
            // -------------------------
            $vip2Cash = (int) round($baseCash * 3.8);
            $vip2Xp = (int) round($baseXp * 2.2);
            $vip2Respect = (int) round($baseRespect * 2.8);
            $vip2Strength = (int) round($baseStrength * 2.4);

            if ($isFinale) {
                $vip2Rewards = [
                    'cash' => 750000,
                    'xp' => 8500,
                    'strength' => 100,
                    'respect' => 450,
                    'vault_tokens' => 10,
                    'stars' => 20,
                    'masks' => 2,
                    'xxl_cases' => 1,
                ];
            } elseif ($isMilestone10) {
                $vip2Rewards = [
                    'cash' => (int) round($vip2Cash * 1.5),
                    'xp' => $vip2Xp,
                    'strength' => $vip2Strength + 8,
                    'respect' => $vip2Respect + 30,
                    'vault_tokens' => 4,
                    'stars' => 7,
                    'masks' => 1,
                ];
            } elseif ($isMilestone5) {
                $vip2Rewards = [
                    'cash' => (int) round($vip2Cash * 1.25),
                    'xp' => $vip2Xp,
                    'strength' => $vip2Strength,
                    'vault_tokens' => 3,
                    'stars' => 5,
                    'unopened_bags' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                $vip2Rewards = [
                    'cash' => $vip2Cash,
                    'strength' => $vip2Strength + 4,
                    'xp' => $vip2Xp,
                    'respect' => $vip2Respect,
                    'stars' => 3,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                $vip2Rewards = [
                    'cash' => $vip2Cash,
                    'vault_tokens' => 3,
                    'xp' => $vip2Xp,
                    'strength' => max(4, (int) round($vip2Strength * 0.8)),
                    'stars' => 3,
                ];
            } else {
                $vip2Rewards = [
                    'cash' => $vip2Cash,
                    'xp' => $vip2Xp,
                    'respect' => $vip2Respect,
                    'strength' => $vip2Strength,
                    'vault_tokens' => 1,
                    'stars' => 2 + ($lvl % 2),
                ];
            }
            $vip2Rewards = array_filter($vip2Rewards, fn (int $v): bool => $v > 0);

            // -------------------------
            // TRACK 4: VIP 3 PASS
            // -------------------------
            $vip3Cash = $baseCash * 7;
            $vip3Xp = (int) round($baseXp * 3.2);
            $vip3Respect = $baseRespect * 4;
            $vip3Strength = (int) round($baseStrength * 3.5);

            if ($isFinale) {
                $vip3Rewards = [
                    'cash' => 1500000,
                    'xp' => 15000,
                    'strength' => 160,
                    'respect' => 800,
                    'vault_tokens' => 15,
                    'stars' => 35,
                    'masks' => 2,
                    'xxl_cases' => 2,
                ];
            } elseif ($isMilestone10) {
                $vip3Rewards = [
                    'cash' => (int) round($vip3Cash * 1.5),
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength + 12,
                    'respect' => $vip3Respect + 50,
                    'vault_tokens' => 6,
                    'stars' => 12,
                    'masks' => 1,
                    'xxl_cases' => 1,
                ];
            } elseif ($isMilestone5) {
                $vip3Rewards = [
                    'cash' => (int) round($vip3Cash * 1.25),
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength,
                    'respect' => $vip3Respect,
                    'vault_tokens' => 4,
                    'stars' => 8,
                    'masks' => 1,
                ];
            } elseif (in_array($mod, [2, 7], true)) {
                $vip3Rewards = [
                    'cash' => $vip3Cash,
                    'strength' => $vip3Strength + 6,
                    'xp' => $vip3Xp,
                    'respect' => $vip3Respect,
                    'vault_tokens' => 2,
                    'stars' => 5,
                ];
            } elseif (in_array($mod, [3, 8], true)) {
                $vip3Rewards = [
                    'cash' => $vip3Cash,
                    'vault_tokens' => 4,
                    'xp' => $vip3Xp,
                    'strength' => $vip3Strength,
                    'respect' => $vip3Respect,
                    'stars' => 5,
                ];
            } else {
                $vip3Rewards = [
                    'cash' => $vip3Cash,
                    'xp' => $vip3Xp,
                    'respect' => $vip3Respect,
                    'strength' => $vip3Strength,
                    'vault_tokens' => 2,
                    'stars' => 4 + ($lvl % 2),
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
                        'title' => $isFinale ? 'VIP 1 Seizoen Kampioen' : $vip1Titles[$mod][0],
                        'icon' => $isFinale ? 'fa-crown' : $vip1Titles[$mod][1],
                        'rewards' => $vip1Rewards,
                        'summary' => self::formatSummary($vip1Rewards),
                    ],
                    'vip2' => [
                        'title' => $isFinale ? 'VIP 2 Gangster Elite Keizer' : $vip2Titles[$mod][0],
                        'icon' => $isFinale ? 'fa-gem' : $vip2Titles[$mod][1],
                        'rewards' => $vip2Rewards,
                        'summary' => self::formatSummary($vip2Rewards),
                    ],
                    'vip3' => [
                        'title' => $isFinale ? 'VIP 3 Kliniek Baas Legende' : $vip3Titles[$mod][0],
                        'icon' => $isFinale ? 'fa-dragon' : $vip3Titles[$mod][1],
                        'rewards' => $vip3Rewards,
                        'summary' => self::formatSummary($vip3Rewards),
                    ],
                ],
            ];
        }

        return $levels;
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
            'vault_tokens' => fn (int $v): string => $v.'x Kluiscodex',
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
     * Display the Battle Pass page with all 60 levels and 4 tracks.
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

        // Calculate how many claimable rewards are waiting
        $claimableCount = 0;
        foreach ($config as $lvlNum => $data) {
            $isUnlocked = $character->level >= $data['req_level'];
            if (! $isUnlocked) {
                continue;
            }

            if (! in_array("lvl_{$lvlNum}_free", $claims, true)) {
                $claimableCount++;
            }
            if ($isVip && $supporterTier >= 1 && ! in_array("lvl_{$lvlNum}_vip1", $claims, true)) {
                $claimableCount++;
            }
            if ($isVip && $supporterTier >= 2 && ! in_array("lvl_{$lvlNum}_vip2", $claims, true)) {
                $claimableCount++;
            }
            if ($isVip && $supporterTier >= 3 && ! in_array("lvl_{$lvlNum}_vip3", $claims, true)) {
                $claimableCount++;
            }
        }

        return view('game.battlepass.index', compact('character', 'config', 'claims', 'supporterTier', 'isVip', 'claimableCount'));
    }

    /**
     * Claim an individual Battle Pass reward.
     */
    public function claim(Request $request): RedirectResponse
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
            return back()->with('error', 'Ongeldig Battle Pass niveau of spoor.');
        }

        $levelData = $config[$lvl];
        $trackData = $levelData['tracks'][$track];

        // Check level requirement
        if ($character->level < $levelData['req_level']) {
            return back()->with('error', "Je moet minimaal Level {$levelData['req_level']} hebben bereikt om dit niveau te ontgrendelen.");
        }

        // Check VIP tier requirement
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        if ($track === 'vip1' && (! $isVip || $supporterTier < 1)) {
            return back()->with('error', 'Voor dit spoor heb je minimaal VIP 1 (TBS V.I.P. Lid) status nodig.');
        }

        if ($track === 'vip2' && (! $isVip || $supporterTier < 2)) {
            return back()->with('error', 'Voor dit spoor heb je minimaal VIP 2 (Gangster Elite) status nodig.');
        }

        if ($track === 'vip3' && (! $isVip || $supporterTier < 3)) {
            return back()->with('error', 'Voor dit spoor heb je minimaal VIP 3 (Kliniek Baas) status nodig.');
        }

        $perks = $character->perks ?? [];
        $claims = $perks['battle_pass_claims'] ?? [];
        $claimKey = "lvl_{$lvl}_{$track}";

        if (in_array($claimKey, $claims, true)) {
            return back()->with('info', 'Je hebt deze beloning al geclaimd.');
        }

        // Record claim first, then grant rewards (including XP level-ups)
        $claims[] = $claimKey;
        $perks['battle_pass_claims'] = $claims;
        $character->perks = $perks;

        $this->applyRewards($character, $trackData['rewards']);
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_pass_claimed',
            'icon' => $trackData['icon'],
            'title' => "Battle Pass Level {$lvl} Geclaimd!",
            'message' => "Je hebt {$trackData['summary']} ontvangen op je account!",
            'is_read' => false,
        ]);

        return back()->with('success', "🎉 Battle Pass Level {$lvl} ({$trackData['title']}) geclaimd: {$trackData['summary']}!");
    }

    /**
     * Claim all available unlocked rewards in one click.
     */
    public function claimAll(Request $request): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;
        $config = self::getPassConfig();

        $perks = $character->perks ?? [];
        $claims = $perks['battle_pass_claims'] ?? [];
        $supporterTier = $character->getSupporterTier();
        $isVip = $character->isVip();

        $claimedCount = 0;
        $aggregatedRewards = [];

        foreach ($config as $lvlNum => $data) {
            if ($character->level < $data['req_level']) {
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
                if (! in_array($claimKey, $claims, true)) {
                    $rewards = $data['tracks'][$track]['rewards'];
                    foreach ($rewards as $key => $amount) {
                        $aggregatedRewards[$key] = ($aggregatedRewards[$key] ?? 0) + $amount;
                    }

                    $claims[] = $claimKey;
                    $claimedCount++;
                }
            }
        }

        if ($claimedCount === 0) {
            return back()->with('info', 'Er zijn op dit moment geen ongeclaimde beloningen die voor jou ontgrendeld zijn.');
        }

        $perks['battle_pass_claims'] = $claims;
        $character->perks = $perks;

        $this->applyRewards($character, $aggregatedRewards);
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'battle_pass_claimed',
            'icon' => 'fa-trophy',
            'title' => "{$claimedCount} Battle Pass Beloningen Geclaimd!",
            'message' => 'Alle ontgrendelde Battle Pass niveaus zijn direct bijgeschreven op je profiel!',
            'is_read' => false,
        ]);

        return back()->with('success', "🎉 Gefeliciteerd! Je hebt in één klik {$claimedCount} Battle Pass beloningen geclaimd (".self::formatSummary($aggregatedRewards).')!');
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
        if (isset($rewards['strength'])) {
            $character->strength = ($character->strength ?? 0) + $rewards['strength'];
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
        if (isset($rewards['xp']) && $rewards['xp'] > 0) {
            $this->levelService->addXp($character, $rewards['xp']);
        }
    }
}
