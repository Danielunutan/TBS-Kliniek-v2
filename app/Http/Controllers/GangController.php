<?php

namespace App\Http\Controllers;

use App\Models\Gang;
use App\Models\User;
use App\Services\EnergyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GangController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Show gang listing and player gang details.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $gangs = Gang::with(['leader', 'members'])->withCount('members')->orderByDesc('treasury')->get();
        $myGang = $character->gang;

        return view('game.gangs', compact('character', 'gangs', 'myGang'));
    }
}
