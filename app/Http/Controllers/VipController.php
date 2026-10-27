<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                $hours = $lastReward ? min(24, (int) $lastReward->diffInHours(now())) : 1;
                $tier = (int) ($character->perks['supporter_tier'] ?? 1);
                $hourlyCashBonus = match ($tier) {
                    4 => 50000,
                    3 => 20000,
                    2 => 7500,
                    default => 2500,
                };

                $earnedPoints = 5 * $hours;
                $earnedCash = $hourlyCashBonus * $hours;

                $character->vip_points = ($character->vip_points ?? 0) + $earnedPoints;
                $character->bank = ($character->bank ?? 0) + $earnedCash;
                $character->last_vip_reward_at = now();
                $character->save();

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
            'reward_type' => 'required|string|in:cash_protection,energy_restore,respect_boost,vip_extend_24h,vip_lootbag,stars_pack',
        ]);

        $rewards = [
            'cash_protection' => [
                'cost' => 20,
                'name' => '24 Uur Cash Bescherming',
                'description' => 'Je cash is 24 uur beschermd tegen diefstal bij aanvallen.',
            ],
            'energy_restore' => [
                'cost' => 10,
                'name' => 'Direct Volledig Herstel',
                'description' => '100% Energie en Gezondheid direct hersteld.',
            ],
            'respect_boost' => [
                'cost' => 15,
                'name' => '+50 Kliniek Respect',
                'description' => 'Direct +50 extra respect op je dossier.',
            ],
            'vip_extend_24h' => [
                'cost' => 20,
                'name' => '+24 Uur VIP Status',
                'description' => 'Verleng of activeer direct 24 uur VIP status met alle voordelen.',
            ],
            'vip_lootbag' => [
                'cost' => 25,
                'name' => 'VIP Buitpakket (XXL Cases + €500.000)',
                'description' => '2x XXL Cases en €500.000 contant geld.',
            ],
            'stars_pack' => [
                'cost' => 30,
                'name' => '100 Exclusieve Sterren',
                'description' => 'Direct +100 sterren voor bende- of kofferupgrades.',
            ],
        ];

        $reward = $rewards[$validated['reward_type']];
        $cost = $reward['cost'];

        if (($character->vip_points ?? 0) < $cost) {
            return back()->with('error', "Niet genoeg VIP Punten! Je hebt {$character->vip_points} punten, maar {$cost} punten zijn vereist.");
        }

        $character->vip_points -= $cost;

        switch ($validated['reward_type']) {
            case 'cash_protection':
                $character->protection_until = now()->addHours(24);
                break;
            case 'energy_restore':
                $character->energy = $character->max_energy;
                $character->health = $character->max_health;
                break;
            case 'respect_boost':
                $character->respect += 50;
                break;
            case 'vip_extend_24h':
                $character->is_vip = true;
                $character->vip_expires_at = ($character->vip_expires_at && $character->vip_expires_at->isFuture())
                    ? $character->vip_expires_at->addDay()
                    : now()->addDay();
                break;
            case 'vip_lootbag':
                $character->xxl_cases += 2;
                $character->cash += 500000;
                break;
            case 'stars_pack':
                $character->stars += 100;
                break;
        }

        $character->save();

        return back()->with('success', "🎉 Gefeliciteerd! Je hebt succesvol '{$reward['name']}' geclaimd voor {$cost} VIP Punten!");
    }

    /**
     * Update VIP custom styling (name color and glitter sparkle effect).
     */
    public function updateStyling(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        if (! $character->isVip()) {
            return back()->with('error', 'Je hebt een actief VIP lidmaatschap nodig om je VIP styling aan te passen.');
        }

        $validated = $request->validate([
            'custom_name_color' => 'nullable|string|max:30',
            'sparkle_enabled' => 'nullable|boolean',
        ]);

        $perks = $character->perks ?? [];

        // Check eligibility
        if ($character->canCustomizeNameColor()) {
            $allowedColors = [
                '#f59e0b', // Goudgeel / Amber
                '#a855f7', // TBS Paars Neon
                '#10b981', // Smaragd Neon
                '#ef4444', // Bloedrood
                '#06b6d4', // Cyber Cyaan
                '#ec4899', // Hot Pink
                '#3b82f6', // Elektrisch Blauw
                '#e2e8f0', // Platina Wit
            ];

            $chosenColor = $validated['custom_name_color'] ?? null;
            if ($chosenColor && in_array($chosenColor, $allowedColors, true)) {
                $perks['custom_name_color'] = $chosenColor;
            } else {
                $perks['custom_name_color'] = null; // Reset to default
            }
        }

        if ($character->canUseSparkleEffect()) {
            $perks['sparkle_enabled'] = (bool) ($request->input('sparkle_enabled', false));
        }

        $character->perks = $perks;
        $character->save();

        return back()->with('success', 'Jouw VIP styling instellingen zijn direct opgeslagen!');
    }
}
