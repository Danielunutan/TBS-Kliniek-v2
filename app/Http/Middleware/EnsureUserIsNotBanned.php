<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBanned()) {
            // Allow logging out
            if ($request->routeIs('logout') || $request->is('logout')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Toegang ontzegd. Je patiëntaccount is verbannen van TBS Kliniek.',
                    'reason' => $user->ban_reason,
                    'banned_until' => $user->banned_until?->toIso8601String(),
                    'is_permanent' => $user->banned_until === null,
                ], 403);
            }

            return response()->view('errors.banned', [
                'user' => $user,
            ], 403);
        }

        return $next($request);
    }
}
