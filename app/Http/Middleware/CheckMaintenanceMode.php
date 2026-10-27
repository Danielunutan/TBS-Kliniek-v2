<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Filepath for the maintenance state lock file.
     */
    public static function lockFilePath(): string
    {
        return storage_path('framework/tbs_maintenance.json');
    }

    /**
     * Check if maintenance mode is actively enabled.
     */
    public static function isEnabled(): bool
    {
        return file_exists(self::lockFilePath());
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always pass through static assets, health check, webhooks, api, and vite dev server
        if ($request->is('build/*', 'css/*', 'js/*', 'images/*', 'favicon.ico', 'up', '@vite/*', 'webhooks/*', 'api/*')) {
            return $next($request);
        }

        // If maintenance is not enabled, continue normally
        if (! self::isEnabled()) {
            return $next($request);
        }

        // Check if Daniel is accessing via bypass query param
        if ($request->query('bypass') === 'daniel') {
            cookie()->queue(cookie()->forever('tbs_maintenance_bypass', 'daniel-tbs-secret-access'));

            return $next($request);
        }

        // Check bypass cookie
        if ($request->cookie('tbs_maintenance_bypass') === 'daniel-tbs-secret-access') {
            return $next($request);
        }

        // Check if the currently authenticated user is Daniel or the platform owner
        $user = $request->user();
        if ($user) {
            $isDaniel = $user->isOwner()
                || strtolower($user->name) === 'daniel'
                || strtolower($user->name) === 'daniël'
                || in_array(strtolower($user->email), ['danielunutan@icloud.com', 'admin@tbskliniek.nl'], true)
                || ($user->character && in_array(mb_strtolower($user->character->name), ['daniel', 'daniël'], true));

            if ($isDaniel) {
                return $next($request);
            }
        }

        // For API / JSON requests, return a clean JSON 503 response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'maintenance' => true,
                'message' => 'TBS Kliniek is tijdelijk in onderhoud voor een grote seizoensreset. We zijn rond 22.00 uur weer terug!',
                'target_time' => '22:00',
            ], 503);
        }

        // Read metadata if available
        $metadata = [];
        $lockFile = self::lockFilePath();
        if (file_exists($lockFile)) {
            $decoded = json_decode((string) file_get_contents($lockFile), true);
            if (is_array($decoded)) {
                $metadata = $decoded;
            }
        }

        return response()->view('maintenance', [
            'targetTime' => $metadata['target_time'] ?? '22:00',
            'seasonReset' => true,
        ], 503);
    }
}
