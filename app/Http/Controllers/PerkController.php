<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerkController extends Controller
{
    public const PERKS = [
        'grotere_broekzakken' => [
            'id' => 'grotere_broekzakken',
            'name' => 'Grotere Broekzakken',
            'icon' => 'fa-wallet',
            'color' => 'emerald',
            'cost_stars' => 25,
            'tagline' => 'Permanent +10% Contant Geld',
            'description' => 'Je broeken zijn vakkundig aangepast met geheime diepe binnenzakken. Je scoort permanent 10% meer contant zwart geld bij elke hossel en elke geslaagde zakkenrol-actie.',
            'benefit' => '+10% cash opbrengst bij Zakkenrollen & Hosselen',
        ],
        'kluis_uitbreiding' => [
            'id' => 'kluis_uitbreiding',
            'name' => 'Kluis & Opslag Uitbreiding',
            'icon' => 'fa-vault',
            'color' => 'blue',
            'cost_stars' => 20,
            'tagline' => 'Permanent +25 Opslagcapaciteit',
            'description' => 'Je hebt een extra geheime opbergruimte achter het ventilatierooster van je cel uitgehakt. Je persoonlijke kluiscapaciteit wordt permanent verhoogd met 25 slots.',
            'benefit' => '+25 permanente opslagruimte in je kluis',
        ],
        'goede_advocaat' => [
            'id' => 'goede_advocaat',
            'name' => 'Topadvocaat op Speeddial',
            'icon' => 'fa-scale-balanced',
            'color' => 'purple',
            'cost_stars' => 40,
            'tagline' => 'Permanent 25% Minder Isoleercel',
            'description' => 'Je hebt een gehaaide topadvocaat op speeddial staan. Zodra je in de isoleercel belandt, dient je advocaat direct vormfouten in, waardoor elke toekomstige straftijd permanent met 25% wordt ingekort.',
            'benefit' => '-25% straftijd bij elke isoleercel veroordeling',
        ],
    ];

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $perks = self::PERKS;

        return view('game.perks.index', compact('character', 'perks'));
    }

    public function buy(Request $request, string $perk): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! array_key_exists($perk, self::PERKS)) {
            return back()->with('error', 'Ongeldige perk geselecteerd.');
        }

        if ($character->hasPerk($perk)) {
            return back()->with('error', 'Je bezit deze permanente perk al!');
        }

        $perkData = self::PERKS[$perk];
        $cost = $perkData['cost_stars'];

        if (($character->stars ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} Sterren ⭐ nodig voor deze perk (je hebt er {$character->stars}).");
        }

        $character->stars -= $cost;
        $character->addPerk($perk);
        $character->save();

        return back()->with('success', "🎉 Gefeliciteerd! Je hebt de permanente perk '{$perkData['name']}' succesvol geactiveerd!");
    }
}
