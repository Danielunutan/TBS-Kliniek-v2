<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show player dashboard.
     */
    public function index(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('login');
        }

        // Refresh energy/health timestamps
        $this->energyService->refresh($character);

        $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($character->level);
        $xpPercentage = min(100, max(0, (int) round(($character->xp / max(1, $nextLevelXp)) * 100)));

        $quickMissions = Mission::orderBy('required_level')->take(3)->get();
        $notifications = $character->notifications()->take(5)->get();
        $recentGuestbook = $character->guestbookEntries()->with('author')->take(3)->get();

        return view('game.dashboard', compact(
            'character',
            'nextLevelXp',
            'xpPercentage',
            'quickMissions',
            'notifications',
            'recentGuestbook'
        ));
    }
}
