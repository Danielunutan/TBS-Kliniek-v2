<?php

namespace App\Http\Controllers;

use App\Services\EventCashHuntService;
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
}

