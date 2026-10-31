<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\TheftRecord;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\TheftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TheftController extends Controller
{
    public function __construct(
        protected TheftService $theftService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show crime & theft overview.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $tiers = TheftService::TIERS;
        $recentThefts = TheftRecord::where('thief_id', $character->id)->latest()->take(6)->get();

        // Other patients in the clinic with cash in their pockets
        $victims = Character::where('id', '!=', $character->id)
            ->where('is_in_prison', false)
            ->orderByDesc('cash')
            ->limit(8)
            ->get();

        return view('game.theft', compact('character', 'tiers', 'recentThefts', 'victims'));
    }

    /**
     * Execute theft attempt (either tier or directly on another inmate).
     */
    public function steal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'string'],
            'victim_id' => ['nullable', 'integer', 'exists:characters,id'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet zakkenrollen!');
        }

        if ($character->isComatose()) {
            $mins = ceil($character->getComaRemainingSeconds() / 60);

            return back()->with('error', "⚠️ Je ligt in Intensive Care Coma (nog {$mins} min)! Ontwaak direct via de Sterren Shop.");
        }

        $victim = null;
        if (! empty($validated['victim_id'])) {
            $victim = Character::find($validated['victim_id']);
            if ($victim && $victim->id === $character->id) {
                return back()->with('error', 'Je kunt je eigen zakken niet leegrollen!');
            }
            if ($victim) {
                return app(FightController::class)->attack(new Request([
                    'opponent_id' => $victim->id,
                    'from_profile' => true,
                ]));
            }
        }

        $result = $this->theftService->execute($character, $validated['tier'], $victim);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        if ($result['jailed']) {
            return redirect()->route('prison')->with('error', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
