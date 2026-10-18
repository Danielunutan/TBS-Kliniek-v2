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
            'tagline' => '24 Uur +10% Contant Geld',
            'description' => 'Je broeken zijn vakkundig aangepast met geheime diepe binnenzakken. Je scoort 24 uur lang 10% meer contant zwart geld bij elke hossel en elke geslaagde zakkenrol-actie.',
            'benefit' => '+10% cash opbrengst bij Zakkenrollen & Hosselen (24 uur)',
        ],
        'kluis_uitbreiding' => [
            'id' => 'kluis_uitbreiding',
            'name' => 'Kluis & Opslag Uitbreiding',
            'icon' => 'fa-vault',
            'color' => 'blue',
            'cost_stars' => 20,
            'tagline' => '24 Uur +25 Opslagcapaciteit',
            'description' => 'Je hebt een extra geheime opbergruimte achter het ventilatierooster van je cel uitgehakt. Je persoonlijke kluiscapaciteit wordt 24 uur lang verhoogd met 25 slots.',
            'benefit' => '+25 opslagruimte in je kluis (24 uur)',
        ],
        'goede_advocaat' => [
            'id' => 'goede_advocaat',
            'name' => 'Topadvocaat op Speeddial',
            'icon' => 'fa-scale-balanced',
            'color' => 'purple',
            'cost_stars' => 40,
            'tagline' => '24 Uur 25% Minder Isoleercel',
            'description' => 'Je hebt een gehaaide topadvocaat op speeddial staan. Zodra je in de isoleercel belandt, dient je advocaat direct vormfouten in, waardoor elke straftijd 24 uur lang met 25% wordt ingekort.',
            'benefit' => '-25% straftijd bij elke isoleercel veroordeling (24 uur)',
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
            return back()->with('error', 'Deze perk is momenteel al actief! Je kunt deze weer opnieuw activeren zodra de huidige 24 uur zijn verlopen.');
        }

        $perkData = self::PERKS[$perk];
        $cost = $perkData['cost_stars'];

        if (($character->stars ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} Sterren ⭐ nodig voor deze perk (je hebt er {$character->stars}).");
        }

        $character->stars -= $cost;
        $character->addPerk($perk, 24);
        $character->save();

        return back()->with('success', "🎉 Gefeliciteerd! Je hebt de perk '{$perkData['name']}' succesvol geactiveerd voor 24 uur!");
    }
}
