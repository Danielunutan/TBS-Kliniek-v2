<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\ControlPointLog;
use App\Models\User;
use App\Services\ControlPointService;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ControlPointController extends Controller
{
    public function __construct(
        protected ControlPointService $controlPointService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show overview of all clinic control points, live statuses, and takeover logs.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character) {
            $this->energyService->refresh($character);
        }

        $points = ControlPoint::with(['holder.user', 'holder.gang', 'gang'])
            ->orderBy('id')
            ->get();

        $logs = ControlPointLog::with(['character', 'gang', 'overtakenBy'])
            ->latest('id')
            ->limit(15)
            ->get();

        $userHeldCount = $character
            ? $points->where('current_character_id', $character->id)->count()
            : 0;

        return view('game.control_points.index', [
            'character' => $character,
            'points' => $points,
            'logs' => $logs,
            'userHeldCount' => $userHeldCount,
        ]);
    }

    /**
     * Capture / take over a specific control point.
     */
    public function capture(Request $request, ControlPoint $point): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen controlepunten in de kliniek overnemen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $this->energyService->refresh($character);

        $result = $this->controlPointService->capture($character, $point);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
