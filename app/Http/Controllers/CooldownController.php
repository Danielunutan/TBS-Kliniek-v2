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
        return back()->with('info', 'Cooldowns kunnen nu gratis en direct worden gereset door een korte sponsor advertentie te bekijken! Zo blijven je kostbare Sterren ⭐ behouden voor exclusieve wagens en perks.');
    }
}
