<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\DailyBattleScore;
use App\Models\User;
use App\Services\DailyBattleService;
use App\Services\EnergyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyBattleController extends Controller
{
    public function __construct(
        protected DailyBattleService $battleService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Render the 24-hour escalation battle dashboard.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $battleData = $this->battleService->getBattleData($character);
        $topRankings = Character::getTopRankings(10);

        return view('game.battle.index', [
            'character' => $character,
            'battle' => $battleData['battle'],
            'categories' => $battleData['categories'],
            'secondsUntilReset' => $battleData['secondsUntilReset'],
            'resetTimeFormatted' => $battleData['resetTimeFormatted'],
            'rushHour' => $battleData['rushHour'],
            'focusCategory' => $battleData['focusCategory'],
            'totalEp' => $battleData['totalEp'],
            'milestones' => $battleData['milestones'],
            'topRankings' => $topRankings,
        ]);
    }

    /**
     * Lock in the character's daily focus category for bonus multiplier points.
     */
    public function selectFocus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->battleService->selectFocusCategory($character, $validated['category']);

        return redirect()->route('battle.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Claim a personal escalation milestone reward tier.
     */
    public function claimMilestone(int $tier): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->battleService->claimMilestone($character, $tier);

        return redirect()->route('battle.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Return the full sector ranking list as JSON for the inspection modal.
     */
    public function categoryDetails(string $category): JsonResponse
    {
        if (! isset(DailyBattleService::CATEGORIES[$category])) {
            return response()->json(['error' => 'Onbekende escalatie-sector opgevraagd.'], 404);
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

        $ranked = $scores->map(function ($s, $idx) use ($col, $category) {
            $rawVal = (int) $s->{$col};

            return [
                'rank' => $idx + 1,
                'name' => $s->character?->name ?? 'Onbekend',
                'avatar' => $s->character?->avatar_url ?? asset('assets/characters/avatar_female_1.jpg'),
                'level' => $s->character?->level ?? 1,
                'score' => $rawVal,
                'ep' => $this->battleService->calculateCategoryEp($category, $rawVal),
            ];
        });

        return response()->json([
            'category' => $cat['title'],
            'formula' => $cat['formula_label'],
            'unit' => $cat['unit_label'],
            'scores' => $ranked,
        ]);
    }
}
