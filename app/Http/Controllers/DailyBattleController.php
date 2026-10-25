<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\DailyBattleScore;
use App\Models\User;
use App\Services\DailyBattleService;
use App\Services\EnergyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyBattleController extends Controller
{
    public function __construct(
        protected DailyBattleService $battleService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show the 24 Hour Battle page.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $battleData = $this->battleService->getBattleData($character);

        // Top 10 for bottom podium ranglijst
        $topRankings = Character::getTopRankings(10);

        return view('game.battle.index', [
            'character' => $character,
            'battle' => $battleData['battle'],
            'categories' => $battleData['categories'],
            'secondsUntilReset' => $battleData['secondsUntilReset'],
            'resetTimeFormatted' => $battleData['resetTimeFormatted'],
            'topRankings' => $topRankings,
        ]);
    }

    /**
     * Get full ranking list for a specific category via JSON or modal.
     */
    public function categoryDetails(string $category): JsonResponse
    {
        if (! isset(DailyBattleService::CATEGORIES[$category])) {
            return response()->json(['error' => 'Ongeldige categorie'], 404);
        }

        $cat = DailyBattleService::CATEGORIES[$category];
        $col = $cat['column'];
        $today = Carbon::today()->toDateString();

        $scores = DailyBattleScore::where('battle_date', $today)
            ->where($col, '>', 0)
            ->orderByDesc($col)
            ->with('character')
            ->take(25)
            ->get();

        $ranked = $scores->map(function ($s, $idx) use ($col) {
            return [
                'rank' => $idx + 1,
                'name' => $s->character->name ?? 'Anoniem',
                'avatar' => $s->character?->avatar_url ?? asset('assets/characters/avatar_female_1.jpg'),
                'level' => $s->character->level ?? 1,
                'score' => $s->{$col},
            ];
        });

        return response()->json([
            'category' => $cat['title'],
            'scores' => $ranked,
        ]);
    }
}
