<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EnergyService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrisonController extends Controller
{
    public function __construct(
        protected PrisonService $prisonService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show prison status.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $isImprisoned = $character->isImprisoned();
        $remainingMinutes = $character->remainingPrisonMinutes();

        return view('game.prison', compact('character', 'isImprisoned', 'remainingMinutes'));
    }

    /**
     * Attempt prison break.
     */
    public function escape(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->prisonService->attemptEscape($character, $this->energyService);

        if ($result['success']) {
            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
