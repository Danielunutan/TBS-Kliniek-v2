<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TravelController extends Controller
{
    public const LOCATIONS = [
        'paviljoen-1-gesloten' => [
            'name' => 'Paviljoen 1 (Gesloten)',
            'desc' => 'Zwaar beveiligde vleugel voor onvoorspelbare patiënten met extra tralies en camera\'s.',
            'icon' => 'fa-lock',
            'badge' => 'Gesloten',
        ],
        'paviljoen-2-half-open' => [
            'name' => 'Paviljoen 2 (Half-open)',
            'desc' => 'Rustigere afdeling voor patiënten in fase 2 met gemeenschappelijke huiskamers.',
            'icon' => 'fa-door-open',
            'badge' => 'Half-open',
        ],
        'de-buitenplaats-resocialisatie' => [
            'name' => 'De Buitenplaats (Resocialisatie)',
            'desc' => 'Overgangsafdeling aan de rand van het terrein met zicht op het buitengebied.',
            'icon' => 'fa-tree',
            'badge' => 'Vrijheid',
        ],
        'het-kookhuis-keuken' => [
            'name' => 'Het Kookhuis (Keuken)',
            'desc' => 'Grote instellingskeuken vol pannen, messen en geur van dampende stamppot.',
            'icon' => 'fa-utensils',
            'badge' => 'Kantine',
        ],
        'de-luchtplaats' => [
            'name' => 'De Luchtplaats',
            'desc' => 'Betonnen open binnenplaats omringd door hekwerk en wachttorens. Dé plek voor deals en duels.',
            'icon' => 'fa-sun',
            'badge' => 'PvP Hotspot',
        ],
        'administratie' => [
            'name' => 'Administratie',
            'desc' => 'Kantoren van directie, dossiers en de centrale meldkamer.',
            'icon' => 'fa-building',
            'badge' => 'Staf',
        ],
        'bezoekerscentrum' => [
            'name' => 'Bezoekerscentrum',
            'desc' => 'Beveiligde ontmoetingsruimte voor familie, advocaten en geheime smokkelafspraken.',
            'icon' => 'fa-handshake',
            'badge' => 'Contact',
        ],
        'recreatiezaal' => [
            'name' => 'Recreatiezaal',
            'desc' => 'Ontspanningsruimte met biljarttafels, pingpong en zithoeken vol geruchten.',
            'icon' => 'fa-gamepad',
            'badge' => 'Chill',
        ],
    ];

    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $locations = self::LOCATIONS;
        $travelCost = $this->calculateTravelCost($character);

        return view('game.travel.index', compact('character', 'locations', 'travelCost'));
    }

    public function move(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je kunt je niet verplaatsen zolang je vastzit in de isoleercel!');
        }

        if ($character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand na 10 liquidaties)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe om weer te kunnen bewegen.');
        }

        if ($character->isWingLocked()) {
            $lockRem = $character->getWingLockRemainingSeconds();
            $mins = (int) ceil($lockRem / 60);

            return back()->with('error', "🔒 AFDELING VERGRENDELD! Een rivaal heeft de deuren van {$character->current_location} op slot laten zetten via een Celblok-Lockdown. Je kunt nog {$mins} min. niet wegvluchten!");
        }

        $request->validate([
            'location' => ['required', 'string'],
        ]);

        $locKey = $request->input('location');

        if (! array_key_exists($locKey, self::LOCATIONS)) {
            return back()->with('error', 'Ongeldige afdeling geselecteerd.');
        }

        $locData = self::LOCATIONS[$locKey];
        $currentLoc = $character->current_location ?? 'Paviljoen 1 (Gesloten)';

        if ($currentLoc === $locData['name']) {
            return back()->with('error', "Je bevindt je momenteel al op {$locData['name']}!");
        }

        $cost = $this->calculateTravelCost($character);

        // Deduct bribe money atomically
        if (! $this->economy->deductCash($character, $cost)) {
            return back()->with('error', 'Je hebt niet genoeg contant geld (€'.number_format($cost, 0, ',', '.').') om de bewakers om te kopen!');
        }

        // Update location
        $character->current_location = $locData['name'];
        $character->save();

        // Award XP
        $xpAward = 35;
        $this->level->addXp($character, $xpAward);

        return back()->with('success', 'Je hebt de bewakers omgekocht voor €'.number_format($cost, 0, ',', '.')." en bent succesvol verplaatst naar {$locData['name']}! (+{$xpAward} XP)");
    }

    /**
     * Calculate cost to bribe guards for transport.
     */
    protected function calculateTravelCost($character): int
    {
        $base = 2500 + ($character->level * 150);

        // VIP 50% discount on guard bribes
        if ($character->isVip()) {
            $base = (int) round($base * 0.5);
        }

        return max(500, $base);
    }
}
