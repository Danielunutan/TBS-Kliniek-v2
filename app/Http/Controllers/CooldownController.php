<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CooldownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CooldownController extends Controller
{
    public function __construct(
        protected CooldownService $cooldownService
    ) {}

    /**
     * Skip an active cooldown using Stars ⭐.
     */
    public function skip(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $action = $request->input('action') ?? $request->input('type');

        if (! $action) {
            return back()->with('error', 'Geen actie opgegeven.');
        }

        if (! $this->cooldownService->isOnCooldown($character, $action)) {
            return back()->with('info', 'Er is geen actieve cooldown voor deze actie!');
        }

        $result = $this->cooldownService->skipWithStars($character, $action);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
