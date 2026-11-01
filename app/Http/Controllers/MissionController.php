<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Mission;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\MissionService;
use App\Support\MissionScenarioCatalog;
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
     * Show mission list with scenarios.
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
        $scenarios = array_merge(MissionScenarioCatalog::allById(), MissionScenarioCatalog::scenarios());
        $totalMissionsCount = Mission::count();
        $completedMissionsCount = $character->missions()->where('completed_count', '>', 0)->count();

        return view('game.missions', compact(
            'character',
            'missions',
            'characterProgress',
            'selectedCategory',
            'scenarios',
            'totalMissionsCount',
            'completedMissionsCount'
        ));
    }

    /**
     * Execute a mission with interactive scenario choice.
     */
    public function execute(Request $request, Mission $mission): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel kun je geen missies uitvoeren!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->hasCompletedMission($mission)) {
            return back()->with('error', 'Deze missie is al succesvol voltooid en kan niet opnieuw worden uitgevoerd.');
        }

        $userChoice = $request->input('choice');

        $result = $this->missionService->execute($character, $mission, $userChoice);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        if (! empty($result['jailed']) || $character->fresh()->isImprisoned()) {
            return redirect()->route('prison')->with('error', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
