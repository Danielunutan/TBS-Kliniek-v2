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
        // Always pass through static assets, health check, webhooks, and vite dev server
        if ($request->is('build/*', 'css/*', 'js/*', 'images/*', 'favicon.ico', 'up', '@vite/*', 'webhooks/*', 'api/webhooks/*', 'api/cpalead/*')) {
            return $next($request);
        }

        // If maintenance is not enabled, continue normally
        if (! self::isEnabled()) {
            return $next($request);
        }

        // Guests need the login form to authenticate; other routes remain unavailable until
        // the resulting session has been checked for an explicit administrator role.
        $user = $request->user();
        if (! $user && $request->routeIs('login', 'login.post')) {
            return $next($request);
        }

        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Read metadata if available
        $metadata = [];
        $lockFile = self::lockFilePath();
        if (file_exists($lockFile)) {
            $content = @file_get_contents($lockFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $metadata = $decoded;
                }
            }
        }

        $targetTime = $metadata['target_time'] ?? '22:00';

        // For AJAX, API, or JSON requests, return a clean JSON 503 response
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'maintenance' => true,
                'message' => "TBS Kliniek is tijdelijk in onderhoud voor een grote seizoensreset. We zijn rond {$targetTime} uur weer terug!",
                'target_time' => $targetTime,
            ], 503);
        }

        return response()->view('maintenance', [
            'targetTime' => $targetTime,
            'seasonReset' => true,
        ], 503);
    }
}
