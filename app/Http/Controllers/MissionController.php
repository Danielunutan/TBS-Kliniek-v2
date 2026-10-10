<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\MissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function __construct(
        protected MissionService $missionService,
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show mission list.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $selectedCategory = $request->query('category', 'all');

        $query = Mission::query()->orderBy('sort_order');
        if ($selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }

        $missions = $query->get();
        $characterProgress = $character->missions()->pluck('completed_count', 'mission_id');

        return view('game.missions', compact(
            'character',
            'missions',
            'characterProgress',
            'selectedCategory'
        ));
    }

    /**
     * Execute a mission.
     */
    public function execute(Request $request, Mission $mission): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->missionService->execute($character, $mission);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
