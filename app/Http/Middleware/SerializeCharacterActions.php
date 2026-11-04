<?php

namespace App\Http\Middleware;

use App\Models\Character;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Run the state-changing requests of one player one after the other.
 *
 * Many game actions first read a cooldown or a balance and write the new value later.
 * Two parallel requests can both pass that check, so one click pays out twice.
 * This middleware gives each player one lock for all POST, PUT, PATCH and DELETE requests.
 * The second request waits until the first one is done, then reads the fresh state.
 */
class SerializeCharacterActions
{
    /**
     * Seconds that a request waits for an earlier request of the same player.
     */
    public const DEFAULT_WAIT_SECONDS = 3;

    /**
     * Seconds after which a lock expires on its own, in case a worker dies.
     */
    public const LOCK_TTL_SECONDS = 20;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Reads cannot cause a double payout, so they never wait.
        if (! $user || $request->isMethodSafe()) {
            return $next($request);
        }

        $lock = Cache::lock('character_action_'.$user->id, self::LOCK_TTL_SECONDS);

        try {
            $lock->block((int) config('game.action_lock_wait_seconds', self::DEFAULT_WAIT_SECONDS));
        } catch (LockTimeoutException) {
            return $this->busyResponse($request);
        }

        try {
            // Earlier middleware may have loaded the character before the lock was taken.
            // Drop it so the controller reads the state that the previous request left behind.
            $user->unsetRelation('character');

            $character = $user->character;
            $routeName = $request->route()?->getName();
            if ($character && $character->isActionBlockedByIsolation($routeName)) {
                return $this->isolationBlockedResponse($request, $character);
            }

            return $next($request);
        } finally {
            $lock->release();
        }
    }

    /**
     * Build the response when a player in voluntary isolation attempts a blocked clinic action.
     */
    protected function isolationBlockedResponse(Request $request, Character $character): Response
    {
        $remaining = $character->protection_until?->diffForHumans(null, true) ?? 'enkele minuten';
        $message = "🔒 Je cel zit op slot in Vrijwillige Isolatie (nog {$remaining})! Zolang je in isolatie zit kun je zelf geen misdaden, klussen of acties uitvoeren. Ontgrendel eerst je celdeur als je weer op pad wilt.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_url' => route('isolation.index'),
            ], 403);
        }

        return back()
            ->with('error', $message)
            ->with('error_url', route('isolation.index'));
    }

    /**
     * Build the answer for a request that could not get the lock in time.
     */
    protected function busyResponse(Request $request): Response
    {
        $message = 'Je vorige actie wordt nog verwerkt. Wacht even en probeer het opnieuw.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 429);
        }

        return back()->with('error', $message);
    }
}
