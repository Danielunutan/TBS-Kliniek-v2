<?php

namespace App\Http\Controllers;

use App\Services\EventCashHuntService;
use App\Services\EventDrugsProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Get real-time leaderboard data for the Cash Hunt event.
     */
    public function cashHuntLeaderboard(Request $request): JsonResponse
    {
        $character = Auth::user()?->character;
        $data = EventCashHuntService::getLeaderboardData($character?->id, 15);

        return response()->json($data);
    }

    /**
     * Get real-time leaderboard data for the Drugs Production event.
     */
    public function drugsProductionLeaderboard(Request $request): JsonResponse
    {
        $character = Auth::user()?->character;
        $data = EventDrugsProductionService::getLeaderboardData($character?->id, 15);

        return response()->json($data);
    }
}
