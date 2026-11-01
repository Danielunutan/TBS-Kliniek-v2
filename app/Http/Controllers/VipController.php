<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VipController extends Controller
{
    public function __construct(
        protected EnergyService $energy,
    ) {}

    /**
     * Show VIP overview and rewards shop.
     */
    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        // Check if VIP hourly reward should be distributed
        if ($character->isVip()) {
            $lastReward = $character->last_vip_reward_at;
            if (! $lastReward || $lastReward->diffInHours(now()) >= 1) {
                $earnedPoints = 0;
                $earnedCash = 0;

                // Re-check the timestamp and compute elapsed hours under lock so parallel page loads cannot pay twice.
                $rewardPaid = DB::transaction(function () use ($character, &$earnedPoints, &$earnedCash): bool {
                    /** @var Character $locked */
                    $locked = Character::where('id', $character->id)->lockForUpdate()->first();

                    if ($locked->last_vip_reward_at && $locked->last_vip_reward_at->diffInHours(now()) < 1) {
                        return false;
                    }

                    $hours = $locked->last_vip_reward_at ? min(24, (int) $locked->last_vip_reward_at->diffInHours(now())) : 1;
                    $tier = (int) ($locked->perks['supporter_tier'] ?? 1);
                    $hourlyCashBonus = match ($tier) {
                        4 => 50000,
                        3 => 20000,
                        2 => 7500,
                        default => 2500,
                    };

                    $earnedPoints = 5 * $hours;
                    $earnedCash = $hourlyCashBonus * $hours;

                    $locked->vip_points = ($locked->vip_points ?? 0) + $earnedPoints;
                    $locked->bank = ($locked->bank ?? 0) + $earnedCash;
                    $locked->last_vip_reward_at = now();
                    $locked->save();

                    return true;
                });

                if ($rewardPaid) {
                    $character->refresh();

                    GameNotification::create([
                        'character_id' => $character->id,
                        'type' => 'vip',
                        'icon' => 'fa-crown',
                        'title' => 'VIP Uurbeloning ontvangen!',
                        'message' => "Je hebt +{$earnedPoints} VIP Punten en €".number_format($earnedCash, 0, ',', '.').' uurbonus op je bank ontvangen!',
                        'is_read' => false,
                    ]);
                }
            }
        }

        $vipProgress = $character->getVipAdMilestoneProgress();
        $adsUntilVip = $character->getAdsUntilNextVip();
        $totalAds = (int) ($character->ads_watched_total ?? 0);
        $hasDiscordReward = $character->hasClaimedDiscordReward();

        return view('game.vip.index', compact('character', 'vipProgress', 'adsUntilVip', 'totalAds', 'hasDiscordReward'));
    }

    /**
     * Redeem VIP points for in-game perks and bonuses.
     */
    public function redeem(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        $validated = $request->validate([
            'reward_type' => 'required|string|in:cash_protection,energy_restore,respect_boost,week_vip_stars,vip_lootbag,stars_pack',
        ]);

        $rewards = [
            'cash_protection' => [
                'cost' => 20,
                'currency' => 'vip_points',
                'name' => '24 Uur Cash Bescherming',
                'description' => 'Je cash is 24 uur beschermd tegen diefstal bij aanvallen.',
            ],
            'energy_restore' => [
                'cost' => 10,
                'currency' => 'vip_points',
                'name' => 'Direct Volledig Herstel',
                'description' => '100% Energie en Gezondheid direct hersteld.',
            ],
            'respect_boost' => [
                'cost' => 15,
                'currency' => 'vip_points',
                'name' => '+50 Kliniek Respect',
                'description' => 'Direct +50 extra respect op je dossier.',
            ],
            'week_vip_stars' => [
                'cost' => 500,
                'currency' => 'stars',
                'name' => '+1 Week VIP Status',
                'description' => 'Activeer of verleng direct 7 dagen VIP status voor 500 Gouden Sterren.',
            ],
            'vip_lootbag' => [
                'cost' => 25,
                'currency' => 'vip_points',
                'name' => 'VIP Buitpakket (XXL Cases + €500.000)',
                'description' => '2x XXL Cases en €500.000 contant geld.',
            ],
            'stars_pack' => [
                'cost' => 30,
                'currency' => 'vip_points',
                'name' => '100 Exclusieve Sterren',
                'description' => 'Direct +100 sterren voor bende- of kofferupgrades.',
            ],
        ];

        $reward = $rewards[$validated['reward_type']];
        $cost = $reward['cost'];
        $currency = $reward['currency'] ?? 'vip_points';
        $rewardType = $validated['reward_type'];

        // Check the balance, charge it, and grant the reward under one row lock so parallel requests cannot redeem twice.
        $errorMessage = DB::transaction(function () use ($character, $currency, $cost, $rewardType): ?string {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->isComatose()) {
                return 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';
            }

            if ($locked->isImprisoned()) {
                return 'Vanuit de isoleercel kun je geen VIP-beloningen inwisselen!';
            }

            if ($currency === 'stars') {
                if (($locked->stars ?? 0) < $cost) {
                    return "Niet genoeg Gouden Sterren! Je hebt {$locked->stars} sterren, maar {$cost} sterren zijn vereist.";
                }
                $locked->stars -= $cost;
            } else {
                if (($locked->vip_points ?? 0) < $cost) {
                    return "Niet genoeg VIP Punten! Je hebt {$locked->vip_points} punten, maar {$cost} punten zijn vereist.";
                }
                $locked->vip_points -= $cost;
            }

            switch ($rewardType) {
                case 'cash_protection':
                    $curCashProt = ($locked->cash_protection_until && $locked->cash_protection_until->isFuture())
                        ? $locked->cash_protection_until
                        : now();
                    $locked->cash_protection_until = $curCashProt->copy()->addHours(24);
                    break;
                case 'energy_restore':
                    $locked->energy = $locked->max_energy;
                    $locked->health = $locked->max_health;
                    break;
                case 'respect_boost':
                    $locked->addRespect(50);
                    break;
                case 'week_vip_stars':
                    $hasPermanentVip = ($locked->is_vip && $locked->vip_expires_at === null)
                        || $locked->getVipTier() === 4;
                    $locked->is_vip = true;
                    if (! $hasPermanentVip) {
                        $locked->vip_expires_at = ($locked->vip_expires_at && $locked->vip_expires_at->isFuture())
                            ? $locked->vip_expires_at->copy()->addDays(7)
                            : now()->addDays(7);
                    }
                    break;
                case 'vip_lootbag':
                    $locked->xxl_cases = ($locked->xxl_cases ?? 0) + 2;
                    $locked->unopened_bags = ($locked->unopened_bags ?? 0) + 2;
                    $locked->cash += 500000;
                    break;
                case 'stars_pack':
                    $locked->stars = ($locked->stars ?? 0) + 100;
                    break;
            }

            $locked->save();

            return null;
        });

        if ($errorMessage !== null) {
            return back()->with('error', $errorMessage);
        }

        $character->refresh();

        $currencyLabel = $currency === 'stars' ? 'Gouden Sterren ⭐' : 'VIP Punten';

        if ($validated['reward_type'] === 'cash_protection') {
            return back()->with('success', "🛡️ 24 Uur Cash Bescherming actief! Je contante geld is tot {$character->cash_protection_until->format('d-m-Y H:i')} 100% beschermd tegen diefstal bij aanvallen.");
        }

        return back()->with('success', "🎉 Gefeliciteerd! Je hebt succesvol '{$reward['name']}' geclaimd voor {$cost} {$currencyLabel}!");
    }

    /**
     * Update VIP custom styling (name color, gradient, glow, and glitter sparkle effect).
     */
    public function updateStyling(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        if (! $character->isVip() && ! Auth::user()->isStaff()) {
            return back()->with('error', 'Je hebt een actief VIP lidmaatschap nodig om je VIP styling aan te passen.');
        }

        $validated = $request->validate([
            'custom_name_color' => 'nullable|string|max:30',
            'custom_name_gradient' => 'nullable|string|max:40',
            'custom_name_glow' => 'nullable|string|max:30',
            'sparkle_enabled' => 'nullable|boolean',
        ]);

        $perks = $character->perks ?? [];

        if ($character->canCustomizeNameColor()) {
            $allowedColors = array_keys(Character::nameColorCatalog());
            $chosenColor = $validated['custom_name_color'] ?? null;
            $perks['custom_name_color'] = ($chosenColor && in_array($chosenColor, $allowedColors, true)) ? $chosenColor : null;

            $allowedGradients = array_keys(Character::nameGradientCatalog());
            $chosenGradient = $validated['custom_name_gradient'] ?? 'none';
            $perks['custom_name_gradient'] = in_array($chosenGradient, $allowedGradients, true) ? $chosenGradient : 'none';

            $allowedGlows = array_keys(Character::nameGlowCatalog());
            $chosenGlow = $validated['custom_name_glow'] ?? 'none';
            $perks['custom_name_glow'] = in_array($chosenGlow, $allowedGlows, true) ? $chosenGlow : 'none';
        }

        if ($character->canUseSparkleEffect()) {
            $perks['sparkle_enabled'] = (bool) ($request->input('sparkle_enabled', false));
        }

        $character->perks = $perks;
        $character->save();

        return back()->with('success', '✨ Jouw naamkleur, kleuroverloop & glow instellingen zijn direct opgeslagen!');
    }
}
