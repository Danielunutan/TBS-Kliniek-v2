<?php

namespace App\Http\Controllers;

use App\Models\TheftRecord;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\TheftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TheftController extends Controller
{
    public function __construct(
        protected TheftService $theftService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show crime & theft overview.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $tiers = TheftService::TIERS;
        $recentThefts = TheftRecord::where('thief_id', $character->id)->latest()->take(6)->get();

        return view('game.theft', compact('character', 'tiers', 'recentThefts'));
    }

    /**
     * Execute theft attempt.
     */
    public function steal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->theftService->execute($character, $validated['tier']);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        if ($result['jailed']) {
            return redirect()->route('prison')->with('error', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
