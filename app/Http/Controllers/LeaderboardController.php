<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\EnergyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Show rankings.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $type = $request->query('type', 'level');

        $query = Character::query()->with('gang');

        match ($type) {
            'respect' => $query->orderByDesc('respect')->orderByDesc('level'),
            'wealth' => $query->selectRaw('*, (cash + bank) as total_wealth')->orderByDesc('total_wealth'),
            'crimes' => $query->orderByDesc('thefts_succeeded')->orderByDesc('respect'),
            default => $query->orderByDesc('level')->orderByDesc('xp'),
        };

        $leaderboard = $query->take(20)->get();
        $topCharacters = $leaderboard;

        return view('game.leaderboard', compact('character', 'leaderboard', 'topCharacters', 'type'));
    }
}
