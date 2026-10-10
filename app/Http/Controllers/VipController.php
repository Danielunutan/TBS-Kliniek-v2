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
                $character->vip_points += 5;
                $character->last_vip_reward_at = now();
                $character->save();

                GameNotification::create([
                    'character_id' => $character->id,
                    'type' => 'vip',
                    'icon' => 'fa-crown',
                    'title' => 'VIP Uurbeloning ontvangen!',
                    'message' => 'Je hebt +5 VIP Punten ontvangen voor je actieve uur in de TBS Kliniek.',
                    'is_read' => false,
                ]);
            }
        }

        return view('game.vip.index', compact('character'));
    }

    /**
     * Redeem VIP points for in-game perks and bonuses.
     */
    public function redeem(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        $validated = $request->validate([
            'reward_type' => 'required|string|in:cash_protection,energy_restore,respect_boost,vip_lootbag,stars_pack',
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
}
