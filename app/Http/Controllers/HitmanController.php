<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HitmanController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        // Potential targets: active players excluding self
        $targets = Character::where('id', '!=', $character->id)
            ->where('is_in_prison', false)
            ->orderByDesc('level')
            ->limit(15)
            ->get();

        $contracts = [
            ['id' => 1, 'name' => 'Waarschuwingsslag',   'desc' => 'Stuur een dreigend pakket. Geen dodelijk letsel, wel schade.', 'cost' => 800,  'energy' => 15, 'damage' => 20, 'rate' => 75, 'xp' => 60],
            ['id' => 2, 'name' => 'Klap bij de kantine', 'desc' => 'Een stevige aframmeling na het avondeten.',                    'cost' => 2000, 'energy' => 20, 'damage' => 35, 'rate' => 65, 'xp' => 110],
            ['id' => 3, 'name' => 'Gewelddadige overval', 'desc' => 'Bewusteloos slaan en beroven op de luchtplaats.',             'cost' => 5000, 'energy' => 25, 'damage' => 55, 'rate' => 50, 'xp' => 200],
        ];

        return view('game.hitman.index', compact('character', 'targets', 'contracts'));
    }

    public function contract(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen liquidatieopdrachten uitvoeren!');
        }

        if (! $request->filled('target_id') && $request->filled('target_name')) {
            $found = Character::where('name', trim($request->input('target_name')))->first();
            if ($found) {
                $request->merge(['target_id' => $found->id]);
            }
        }

        $validated = $request->validate([
            'target_id' => ['required', 'exists:characters,id'],
            'contract_id' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $config = [
            1 => ['cost' => 800,  'energy' => 15, 'damage' => 20, 'rate' => 75, 'xp' => 60,  'label' => 'Waarschuwingsslag'],
            2 => ['cost' => 2000, 'energy' => 20, 'damage' => 35, 'rate' => 65, 'xp' => 110, 'label' => 'Klap bij de kantine'],
            3 => ['cost' => 5000, 'energy' => 25, 'damage' => 55, 'rate' => 50, 'xp' => 200, 'label' => 'Gewelddadige overval'],
        ];

        $contract = $config[$validated['contract_id']];
        $target = Character::findOrFail($validated['target_id']);

        if ($target->id === $character->id) {
            return back()->with('error', 'Je kunt geen contract op jezelf plaatsen!');
        }

        if ($character->cash < $contract['cost']) {
            return back()->with('error', "Dit contract kost {$contract['cost']} KC. Je hebt onvoldoende contant geld.");
        }

        if (! $this->energy->consume($character, $contract['energy'])) {
            return back()->with('error', "Je hebt minimaal {$contract['energy']} energie nodig voor dit contract!");
        }

        $character->cash -= $contract['cost'];
        $character->save();

        $roll = rand(1, 100);
        if ($roll <= $contract['rate']) {
            $target->health = max(0, $target->health - $contract['damage']);
            $target->save();

            $this->level->addXp($character, $contract['xp']);
            $character->respect += 3;
            $character->save();

            GameNotification::create([
                'character_id' => $target->id,
                'type' => 'attack',
                'icon' => 'fa-skull-crossbones',
                'title' => 'Je bent aangevallen!',
                'message' => "Een onbekende heeft een liquidatieopdracht op je uitgevoerd ({$contract['label']}). Je verloor {$contract['damage']} HP.",
            ]);

            return back()->with('success', "Contract succesvol uitgevoerd! {$target->name} verliest {$contract['damage']} HP. +{$contract['xp']} XP, +3 Respect.");
        }

        if (rand(1, 100) <= 40) {
            $this->prison->jail($character, 15, 'Betrapt bij het uitvoeren van een liquidatieopdracht.');

            return redirect()->route('prison')->with('error', 'Contract mislukt! Jij bent opgepakt door de bewaking. 15 minuten isoleercel.');
        }

        return back()->with('error', 'Contract mislukt! Je huurmoordenaar botste op de bewaking en is gevlucht. Je verloor het geld.');
    }
}
