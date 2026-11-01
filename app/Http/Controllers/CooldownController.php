<?php

namespace App\Http\Controllers;

use App\Models\Character;
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
                $action = (string) $request->input('action');
                if (! in_array($action, ['coma', 'coma_revive'], true)) {
                    if ($character->isImprisoned()) {
                        return back()->with('error', 'Vanuit de isoleercel kun je geen actie-cooldowns overslaan!');
                    }
                    if ($character->isComatose()) {
                        return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
                    }
                }

                $result = $this->cooldownService->skipWithStars($character, $action);
                if ($result['success']) {
                    return back()->with('success', $result['message']);
                }

                return back()->with('error', $result['message']);
            }
        }

        return back()->with('error', 'Geen geldige actie opgegeven om de cooldown van over te slaan.');
    }
}
