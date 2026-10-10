<?php

namespace App\Http\Controllers;

use App\Models\TrainingLog;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\TrainingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __construct(
        protected TrainingService $trainingService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show gym/training screen.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $stats = TrainingService::STATS;
        $recentLogs = TrainingLog::where('character_id', $character->id)->latest()->take(5)->get();

        return view('game.training', compact('character', 'stats', 'recentLogs'));
    }

    /**
     * Train an attribute.
     */
    public function train(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stat' => ['required', 'string', 'in:strength,defense,stamina,intelligence,charisma,discipline'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->trainingService->train($character, $validated['stat']);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
