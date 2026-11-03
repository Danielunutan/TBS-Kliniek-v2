<?php

namespace App\Http\Controllers;

use App\Services\EventCashHuntService;
use App\Services\EventDrugsProductionService;
use App\Services\EventVaultCrackingService;
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
        $limit = max(1, min(100, (int) $request->query('limit', 15)));
        $character = Auth::user()?->character;
        $data = EventCashHuntService::getLeaderboardData($character?->id, $limit);

        return response()->json($data);
    }

    /**
     * Get real-time leaderboard data for the Drugs Production event.
     */
    public function drugsProductionLeaderboard(Request $request): JsonResponse
    {
        $limit = max(1, min(100, (int) $request->query('limit', 15)));
        $character = Auth::user()?->character;
        $data = EventDrugsProductionService::getLeaderboardData($character?->id, $limit);

        return response()->json($data);
    }

    /**
     * Get real-time leaderboard data for the Vault Challenge event.
     */
    public function vaultChallengeLeaderboard(Request $request): JsonResponse
    {
        $limit = max(1, min(100, (int) $request->query('limit', 15)));
        $character = Auth::user()?->character;
        $data = EventVaultCrackingService::getLeaderboardData($character?->id, $limit);

        return response()->json($data);
    }
}
