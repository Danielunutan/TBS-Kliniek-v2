<?php

namespace App\Http\Controllers;

use App\Models\TrainingLog;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
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
        $recentLogs = TrainingLog::where('character_id', $character->id)->latest()->take(8)->get();

        return view('game.training', compact('character', 'stats', 'recentLogs'));
    }

    /**
     * Train an attribute.
     */
    public function train(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'stat' => ['required', 'string', 'in:strength,defense,stamina,intelligence,charisma,discipline'],
            'times' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $times = (int) ($validated['times'] ?? 1);
        $totalGain = 0;
        $lastResult = null;

        for ($i = 0; $i < $times; $i++) {
            $result = $this->trainingService->train($character, $validated['stat']);
            if (! $result['success']) {
                if ($i === 0) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $result['message'],
                        ], 422);
                    }

                    return back()->with('error', $result['message']);
                }
                break;
            }

            $totalGain += $result['stat_gain'];
            $lastResult = $result;
            HalloweenService::record($character, 'training', 1);
        }

        $character->refresh();
        $statKey = $validated['stat'];
        $currentVal = (int) $character->{$statKey};
        $nextCost = (int) max(20, round(15 + ($currentVal * 3.5)));
        $statName = TrainingService::STATS[$statKey]['name'];

        $message = $times > 1
            ? "Top workout! Je hebt {$statName} {$totalGain}x getraind naar level {$currentVal}!"
            : $lastResult['message'];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'stat' => $statKey,
                'stat_name' => $statName,
                'stat_gain' => $totalGain,
                'new_value' => $currentVal,
                'next_cost' => $nextCost,
                'cash' => $character->cash,
                'cash_formatted' => '€'.number_format($character->cash, 0, ',', '.'),
                'energy' => $character->energy,
                'max_energy' => $character->max_energy,
                'xp' => $character->xp,
            ]);
        }

        return back()->with('success', $message);
    }
}
