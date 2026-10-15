<?php

namespace App\Http\Middleware;

use App\Services\EveningVaultDropService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserOnlineStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && $character = $user->character) {
                // Update last_active_at if null or if more than 60 seconds have elapsed
                if (! $character->last_active_at || $character->last_active_at->diffInSeconds(now()) >= 60) {
                    $character->timestamps = false;
                    $character->forceFill(['last_active_at' => now()])->saveQuietly();
                }

                // Check and award 22:00 - 22:30 free 3 vault codes drop
                if (app(EveningVaultDropService::class)->checkAndAward($character)) {
                    session()->flash('evening_drop_awarded', true);
                }
            }
        }

        return $next($request);
    }
}
