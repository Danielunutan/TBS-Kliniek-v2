<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrisonController extends Controller
{
    public function __construct(
        protected PrisonService $prisonService,
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show prison status & list of jailed inmates (Matching Screenshot 1).
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $isImprisoned = $character->isImprisoned();
        $remainingMinutes = $character->remainingPrisonMinutes();
        $remainingSeconds = $character->remainingPrisonSeconds();

        // Real prisoners currently locked up
        $realPrisoners = Character::where('is_in_prison', true)
            ->whereNotNull('prison_until')
            ->where('prison_until', '>', now())
            ->where('id', '!=', $character->id)
            ->get();

        // Predefined diverse simulated inmates so table is always full and entertaining
        $simulatedPresets = [
            [
                'id' => 'sim_1',
                'name' => 'Kliniek Patiënt met schulden',
                'reason' => 'Schulden en diefstal in kliniek',
                'time' => '02:05',
                'chance' => 5,
                'avatar' => asset('assets/characters/avatar_female_1.jpg'),
                'is_simulated' => true,
            ],
            [
                'id' => 'sim_2',
                'name' => 'Roxy Deluxe',
                'reason' => 'Coke gesmokkeld in Chanel tas',
                'time' => '02:29',
                'chance' => 5,
                'avatar' => asset('assets/characters/avatar_female_2.jpg'),
                'is_simulated' => true,
            ],
            [
                'id' => 'sim_3',
                'name' => 'Amber Savage',
                'reason' => 'Betrapt bij kantoorkluis kraak',
                'time' => '03:58',
                'chance' => 5,
                'avatar' => asset('assets/characters/avatar_female_3.jpg'),
                'is_simulated' => true,
            ],
            [
                'id' => 'sim_4',
                'name' => 'Chantal Poison',
                'reason' => 'Ruzie en vechtpartij in kantine',
                'time' => '04:25',
                'chance' => 5,
                'avatar' => asset('assets/characters/avatar_female_4.jpg'),
                'is_simulated' => true,
            ],
            [
                'id' => 'sim_5',
                'name' => 'Bella Cashflow',
                'reason' => 'Valse MedicijnKluis codes verspreid',
                'time' => '05:12',
                'chance' => 8,
                'avatar' => asset('assets/characters/avatar_female_5.jpg'),
                'is_simulated' => true,
            ],
        ];

        // Combine real and simulated inmates
        $jailList = collect();

        foreach ($realPrisoners as $rp) {
            $remSecs = max(0, $rp->prison_until->diffInSeconds(now()));
            $jailList->push([
                'id' => $rp->id,
                'name' => $rp->name,
                'reason' => $rp->prison_reason ?? 'Wangedrag in de kliniek',
                'time' => gmdate('i:s', $remSecs),
                'chance' => 5,
                'avatar' => $rp->avatar_url,
                'is_simulated' => false,
                'model' => $rp,
            ]);
        }

        foreach ($simulatedPresets as $sim) {
            $jailList->push($sim);
        }

        // Breakout Stats for character
        $stats = [
            'escapes_today' => $character->prison_escapes ?? 0,
            'attempts_today' => $character->breakout_attempts ?? 0,
            'breakout_xp' => '0.1%',
            'slay' => number_format((float) ($character->breakout_slay ?? 0), 1),
            'best_day_date' => now()->format('d-m-Y'),
            'best_day_count' => max(2, $character->prison_escapes ?? 0),
        ];

        // Top uitbrekers van de dag leaderboard
        $topBreakers = Character::orderByDesc('prison_escapes')
            ->take(5)
            ->get();

        if ($topBreakers->isEmpty() || $topBreakers->first()->prison_escapes === 0) {
            $mockBreakers = [
                ['name' => 'Zaphira', 'escapes' => 301, 'avatar' => asset('assets/characters/avatar_female_2.jpg')],
                ['name' => 'PoisonIvy', 'escapes' => 296, 'avatar' => asset('assets/characters/avatar_female_3.jpg')],
                ['name' => 'NightShade', 'escapes' => 116, 'avatar' => asset('assets/characters/avatar_female_4.jpg')],
            ];
        } else {
            $mockBreakers = $topBreakers->map(fn ($p) => [
                'name' => $p->name,
                'escapes' => max(1, $p->prison_escapes),
                'avatar' => $p->avatar_url,
            ])->toArray();
        }

        // Top 10 for bottom podium ranglijst
        $topRankings = Character::orderByDesc('respect')->take(10)->get();

        return view('game.prison', compact(
            'character',
            'isImprisoned',
            'remainingMinutes',
            'remainingSeconds',
            'jailList',
            'stats',
            'mockBreakers',
            'topRankings'
        ));
    }

    /**
     * Attempt prison break for self.
     */
    public function escape(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->prisonService->attemptEscape($character, $this->energyService);

        if ($result['success']) {
            $character->prison_escapes++;
            $character->breakout_slay = ($character->breakout_slay ?? 0) + 0.1;
            $character->save();

            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Buy self out of prison using Stars.
     */
    public function buyout(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->isImprisoned()) {
            return back()->with('info', 'Je zit momenteel niet in de isoleercel!');
        }

        $cost = 2;
        if (($character->stars ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} Sterren ⭐ nodig om jezelf vrij te kopen (je hebt er {$character->stars}). Koop ze in de Shop of op de Marktplaats!");
        }

        $character->stars -= $cost;
        $character->is_in_prison = false;
        $character->prison_until = null;
        $character->prison_reason = null;
        $character->save();

        return back()->with('success', "🔓 Vrijgekocht voor {$cost} Sterren ⭐! Je celdeur is ontgrendeld en je bent weer op vrije voeten!");
    }

    /**
     * Attempt to break out another inmate (real or simulated).
     */
    public function breakout(Request $request, string $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Je zit zelf in de isoleercel!');
        }

        $energyCost = 15;
        if (! $this->energyService->consume($character, $energyCost)) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor een uitbraakpoging!");
        }

        $character->breakout_attempts = ($character->breakout_attempts ?? 0) + 1;
        $character->save();

        $roll = rand(1, 100);
        $successRate = 8; // ~8% suspenseful chance matching 5% display

        if ($roll <= $successRate) {
            $character->prison_escapes = ($character->prison_escapes ?? 0) + 1;
            $character->breakout_slay = ($character->breakout_slay ?? 0) + 0.1;
            $character->respect += 2;
            $this->levelService->addXp($character, 140);
            $character->save();

            // If real inmate, release them
            if (is_numeric($id)) {
                $realPrisoner = Character::find($id);
                if ($realPrisoner && $realPrisoner->isImprisoned()) {
                    $realPrisoner->is_in_prison = false;
                    $realPrisoner->prison_until = null;
                    $realPrisoner->prison_reason = null;
                    $realPrisoner->save();

                    GameMessage::create([
                        'recipient_id' => $realPrisoner->id,
                        'sender_id' => $character->id,
                        'sender_name' => $character->name,
                        'subject' => '🔓 VRIJ! Je bent bevrijd uit de gevangenis!',
                        'content' => "Patiënt {$realPrisoner->name},\n\n{$character->name} heeft zojuist met gevaar voor eigen leven de isoleerceldeur geforceerd en jou bevrijd!",
                        'type' => 'gang',
                    ]);
                }
            }

            return back()->with('success', '🎉 RAAK! De celdeur zwaait open! Patiënt succesvol uitgebroken! +0.1 Respect 🔥 en +140 XP verdiend!');
        }

        // On failure: caught in the act!
        // Lock player in prison for e.g. 120-135 seconds (dynamic around 126 sec)
        $jailSeconds = random_int(120, 135);
        $this->prisonService->jailSeconds($character, $jailSeconds, 'Betrapt bij uitbraakpoging van een patiënt in de isoleercel.');

        return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie!");
    }
}
