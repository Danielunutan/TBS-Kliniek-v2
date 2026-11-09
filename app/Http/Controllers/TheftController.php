<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
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
    public function index(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        /** @var Character|null $character */
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        $this->energyService->refresh($character);

        $tiers = TheftService::TIERS;
        $recentThefts = TheftRecord::where('thief_id', $character->id)->latest()->take(6)->get();

        // Other patients in the clinic with cash in their pockets
        $victims = Character::where('id', '!=', $character->id)
            ->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES)
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
        /** @var User|null $user */
        $user = Auth::user();
        /** @var Character|null $character */
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet zakkenrollen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'tier' => ['required', 'string', 'in:'.implode(',', array_keys(TheftService::TIERS))],
            'victim_id' => ['nullable', 'integer', 'exists:characters,id'],
            'confirm_break_isolation' => ['nullable', 'boolean'],
        ]);

        if ($character->isInVoluntaryIsolation()) {
            if (! $request->boolean('confirm_break_isolation')) {
                $remaining = $character->protection_until ? $character->protection_until->diffForHumans(null, true) : 'onbekend';

                return back()
                    ->with('error', "🔒 Je cel is vergrendeld in Vrijwillige Isolatie (nog {$remaining})! Bevestig dat je je isolatie wilt beëindigen om deze diefstal uit te voeren.")
                    ->with('error_url', route('isolation.index'));
            }

            $character->breakProtection();
            GameNotification::create([
                'character_id' => $character->id,
                'type' => 'warning',
                'icon' => 'fa-door-open',
                'title' => 'Isolatie Vroegtijdig Beëindigd',
                'message' => 'Je hebt je cel verlaten om een beroving uit te voeren. Je isolatiebescherming is beëindigd.',
            ]);
        }

        $victim = null;
        if (! empty($validated['victim_id'])) {
            $victim = Character::find($validated['victim_id']);
            if ($victim && $victim->id === $character->id) {
                return back()->with('error', 'Je kunt je eigen zakken niet leegrollen!');
            }
            if ($victim && in_array($victim->name, Character::EXCLUDED_SYSTEM_NAMES, true)) {
                return back()->with('error', 'Dit doelwit kan niet worden bestolen!');
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
