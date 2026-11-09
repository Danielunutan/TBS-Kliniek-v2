<?php

namespace App\Http\Middleware;

use App\Models\GameSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Filepath for the maintenance state lock file.
     * Uses an isolated test lock file when running under PHPUnit so live maintenance state is never wiped.
     */
    public static function lockFilePath(): string
    {
        if (app()->runningUnitTests()) {
            return storage_path('framework/testing/tbs_maintenance_test.json');
        }

        return storage_path('framework/tbs_maintenance.json');
    }

    /**
     * Check if maintenance mode is actively enabled via lock file or database setting.
     */
    public static function isEnabled(): bool
    {
        if (file_exists(self::lockFilePath())) {
            return true;
        }

        if (app()->runningUnitTests()) {
            return false;
        }

        try {
            if (Schema::hasTable('game_settings')) {
                $dbState = GameSetting::get('maintenance_mode_enabled', false);

                return filter_var($dbState, FILTER_VALIDATE_BOOLEAN);
            }
        } catch (\Throwable) {
            // Fall back to lock file check when the database is unavailable.
        }

        return false;
    }

    /**
     * Retrieve maintenance metadata from the lock file or database setting.
     *
     * @return array<string, mixed>
     */
    public static function getMetadata(): array
    {
        $lockFile = self::lockFilePath();
        if (file_exists($lockFile)) {
            $content = @file_get_contents($lockFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        if (app()->runningUnitTests()) {
            return [];
        }

        try {
            if (Schema::hasTable('game_settings')) {
                $meta = GameSetting::get('maintenance_mode_metadata', []);
                if (is_array($meta)) {
                    return $meta;
                }
            }
        } catch (\Throwable) {
            // Return empty metadata when the database is unavailable.
        }

        return [];
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always pass through static assets, health check, webhooks, and vite dev server
        if ($request->is('build/*', 'css/*', 'js/*', 'images/*', 'assets/*', 'audio/*', 'favicon.ico', 'up', '@vite/*', 'webhooks/*', 'api/webhooks/*', 'api/cpalead/*')) {
            return $next($request);
        }

        // If maintenance is not enabled, continue normally
        if (! self::isEnabled()) {
            return $next($request);
        }

        $user = $request->user();

        // Allow administrators and owners full access during maintenance
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Allow anyone to log out
        if ($request->routeIs('logout') || $request->is('logout')) {
            return $next($request);
        }

        // Allow staff login form (?beheer=1) or login credential submission
        if ($request->routeIs('login.post') || ($request->routeIs('login') && $request->boolean('beheer'))) {
            if ($user && ! $user->isAdmin()) {
                Auth::guard('web')->logout();
                if ($request->isMethod('GET') && $request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            return $next($request);
        }

        // Force logout and invalidate session for any logged-in non-admin player so they are immediately locked out
        if ($user) {
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        // Read metadata if available
        $metadata = self::getMetadata();
        $targetTime = $metadata['target_time'] ?? null;

        // For AJAX, API, or JSON requests, return a clean JSON 503 response with redirect to lock the client onto the maintenance page
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*') || $request->header('X-TBS-Action') === 'true') {
            return response()->json([
                'success' => false,
                'maintenance' => true,
                'reload' => true,
                'redirect' => route('login'),
                'message' => 'TBS Kliniek is tijdelijk in onderhoud! Naar aanleiding van jullie feedback voeren we een volledige en eerlijke reset door.',
                'target_time' => $targetTime,
            ], 503)->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        }

        return response()->view('maintenance', [
            'targetTime' => $targetTime,
            'seasonReset' => true,
        ], 503)->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
