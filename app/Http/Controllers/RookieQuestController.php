<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\RookieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RookieQuestController extends Controller
{
    public function __construct(
        protected RookieService $rookieService
    ) {}

    /**
     * Claim reward for a completed rookie task.
     */
    public function claim(Request $request, string $taskKey): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 404);
        }

        $result = $this->rookieService->claimTaskReward($character, $taskKey);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Complete the intake tour.
     */
    public function completeTour(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 404);
        }

        $result = $this->rookieService->completeTour($character);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }
}
