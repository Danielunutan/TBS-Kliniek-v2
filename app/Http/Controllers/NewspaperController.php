<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Gang;
use App\Models\LotteryRound;
use App\Models\TheftRecord;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NewspaperController extends Controller
{
    /**
     * Show the automated daily clinic newspaper.
     */
    public function index(): View
    {
        /** @var User|null $user */
        $user = Auth::user();
        $character = $user ? $user->character : null;

        // 1. Top Recent Thefts / Heists in the clinic
        $topThefts = TheftRecord::with(['thief', 'victim'])
            ->where('success', true)
            ->where('cash_stolen', '>', 0)
            ->orderByDesc('id')
            ->take(4)
            ->get();

        // 2. Currently Imprisoned Inmates in Isolation
        $imprisonedInmates = Character::playable()
            ->where('is_in_prison', true)
            ->whereNotNull('prison_until')
            ->where('prison_until', '>', now())
            ->orderByDesc('prison_until')
            ->take(5)
            ->get();

        // 3. Most feared fighter on the courtyard
        $topFighter = Character::playable()
            ->orderByDesc('fights_won')
            ->where('fights_won', '>', 0)
            ->first();

        // 4. Most powerful gang in the clinic
        $topGang = Gang::withCount('members')
            ->orderByDesc('treasury')
            ->first();

        // 5. Patient of the day (Random active inmate spotlight)
        $spotlightQuery = Character::playable()->where('level', '>=', 2);
        if ($character) {
            $spotlightQuery->where('id', '!=', $character->id);
        }
        $spotlightPatient = $spotlightQuery->inRandomOrder()->first()
            ?? Character::playable()->first()
            ?? $character
            ?? Character::first();

        // 6. Current lottery jackpot info
        $lotteryRound = LotteryRound::current();

        // 7. Satirical Courtyard Weather forecast based on day of week
        $weatherReports = [
            0 => ['temp' => '14°C', 'sky' => 'Druilerig & Bewolkt', 'icon' => 'fa-cloud-rain', 'comment' => 'Motregen op de luchtplaats. Verhoogde kans op uitglijders bij het hek en warme chocomel in de kantine.'],
            1 => ['temp' => '17°C', 'sky' => 'Grijze Mist', 'icon' => 'fa-smog', 'comment' => 'Dichte mist rond de wachttorens. Ideale omstandigheden om ongemerkt sigaretten over te dragen.'],
            2 => ['temp' => '19°C', 'sky' => 'Half Bewolkt', 'icon' => 'fa-cloud-sun', 'comment' => 'Zachte bries over het asfalt. Bewakers staan ontspannen bij de koffiekar, verhoogde hosselkansen!'],
            3 => ['temp' => '22°C', 'sky' => 'Volle Zon', 'icon' => 'fa-sun', 'comment' => 'Broeierig warm bij de pingpongtafels. Dr. Van Beek adviseert extra hydratatie en kalmte op de bankjes.'],
            4 => ['temp' => '16°C', 'sky' => 'Stevige Windkracht 5', 'icon' => 'fa-wind', 'comment' => 'Hard waaiende wind. Pas op voor wegwaaiende vloei en tabak in de rokerstuin!'],
            5 => ['temp' => '20°C', 'sky' => 'Zonnig & Rustig', 'icon' => 'fa-sun', 'comment' => 'Prettig recreatieweer. De fitnesshoek is overvol en de sfeer tussen de bendes is gespannen maar rustig.'],
            6 => ['temp' => '18°C', 'sky' => 'Wisselvallig', 'icon' => 'fa-cloud-sun-rain', 'comment' => 'Korte plensbuien afgewisseld met zon. Zaalwacht Willem heeft zijn regenjas aangetrokken.'],
        ];
        $todayWeather = $weatherReports[now()->dayOfWeek] ?? $weatherReports[3];

        return view('game.newspaper.index', compact(
            'character',
            'topThefts',
            'imprisonedInmates',
            'topFighter',
            'topGang',
            'spotlightPatient',
            'lotteryRound',
            'todayWeather'
        ));
    }
}
