<?php

namespace App\Http\Controllers;

use App\Services\CooldownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CooldownController extends Controller
{
    public function __construct(
        protected CooldownService $cooldownService
    ) {}

    public function skip(Request $request): RedirectResponse
    {
        if ($request->filled('action')) {
            $character = $request->user()?->character;
            if ($character) {
                $result = $this->cooldownService->skipWithStars($character, (string) $request->input('action'));
                if ($result['success']) {
                    return back()->with('success', $result['message']);
                }

                return back()->with('error', $result['message']);
            }
        }

        return back()->with('info', 'Cooldowns kunnen nu gratis en direct worden gereset door een korte sponsor advertentie te bekijken! Zo blijven je kostbare Sterren ⭐ behouden voor exclusieve wagens en perks.');
    }
}
