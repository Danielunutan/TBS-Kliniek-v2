<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOnboarded
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && (! $user->is_onboarded || ! $user->character || ! $user->character->is_onboarded)) {
            // Allow onboarding routes, logout, legal and static assets
            if (! $request->routeIs('onboarding.*') &&
                ! $request->routeIs('logout') &&
                ! $request->is('logout') &&
                ! $request->is('admin*') &&
                ! $request->is('legal/*') &&
                ! $request->is('up')
            ) {
                return redirect()->route('onboarding.index');
            }
        }

        return $next($request);
    }
}
