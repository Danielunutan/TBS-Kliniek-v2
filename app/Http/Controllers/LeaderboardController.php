<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\EnergyService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        if (! in_array($type, ['level', 'respect', 'wealth', 'crimes'], true)) {
            $type = 'level';
        }

        $cacheKey = "tbs_leaderboard_{$type}_v3";
        $cached = Cache::get($cacheKey);
        if ($cached instanceof \__PHP_Incomplete_Class || ! ($cached instanceof Collection)) {
            Cache::forget($cacheKey);
            Cache::forget("tbs_leaderboard_{$type}");
            $cached = null;
        }

        $leaderboard = $cached ?? (function () use ($type, $cacheKey) {
            $query = Character::query()->with('gang');

            match ($type) {
                'respect' => $query->orderByDesc('respect')->orderByDesc('level'),
                'wealth' => $query->selectRaw('*, (cash + bank) as total_wealth')->orderByDesc('total_wealth'),
                'crimes' => $query->orderByDesc('thefts_succeeded')->orderByDesc('respect'),
                default => $query->orderByDesc('level')->orderByDesc('xp'),
            };

            $result = $query->take(50)->get();
            Cache::put($cacheKey, $result, 60);

            return $result;
        })();

        $topCharacters = $leaderboard;

        return view('game.leaderboard', compact('character', 'leaderboard', 'topCharacters', 'type'));
    }
}
