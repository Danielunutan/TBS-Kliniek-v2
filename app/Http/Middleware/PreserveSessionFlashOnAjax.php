<?php

namespace App\Http\Middleware;

use App\Models\Character;
use App\Services\EnergyService;
use App\Services\FlashNotificationService;
use App\Services\LevelService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class PreserveSessionFlashOnAjax
{
    /**
     * Handle an incoming request.
     *
     * Preserve flash session data across background AJAX polling requests so that
     * flash notifications (success/error banners) are never lost or consumed before
     * the user's browser has rendered the redirected HTML page.
     *
     * Also mirrors flash messages into FlashNotificationService (isolated cache store)
     * to eliminate database session overwrite race conditions from concurrent AJAX polls.
     *
     * In addition, when an action is dispatched via in-app AJAX (X-TBS-Action: true),
     * this middleware intercepts redirects to return seamless JSON with fresh character
     * stats and flash feedback, eliminating full page reloads and scroll jump.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->hasSession() ? $request->session() : null;

        $isAction = $request->header('X-TBS-Action') === 'true';
        $isBackgroundRefresh = $request->header('X-TBS-Background-Refresh') === 'true';

        $isAjaxOrPolling = $request->ajax()
            || $request->expectsJson()
            || $isAction
            || $isBackgroundRefresh
            || $request->is('chat/*')
            || $request->is('cinema/*')
            || $request->is('api/*')
            || $request->is('sponsor/*')
            || $request->is('true-crime/*')
            || $request->is('connect-four/*')
            || $request->is('slots/*')
            || $request->is('casino/*')
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || ! $request->acceptsHtml();

        // 1. Before next middleware: If this is an AJAX / polling request, immediately restore any flash messages aged by StartSession
        // But if this is a background container refresh right after an action, do NOT reflash since the flash was already consumed in the action JSON
        if ($session && $isAjaxOrPolling && ! $isAction && ! $isBackgroundRefresh) {
            $session->reflash();
            $session->keep(['success', 'error', 'status', 'warning', 'info']);
        }

        if ($session && $isBackgroundRefresh) {
            $user = $request->user();
            $identifier = $user?->id ?? $session->getId();
            app(FlashNotificationService::class)->consume($identifier);
            $session->forget(['tbs_flash_success', 'tbs_flash_error', 'tbs_flash_time', 'success', 'error', 'status', 'errors']);
        }

        $response = $next($request);

        // 2. After response:
        if ($session) {
            if ($response->isRedirection()) {
                $user = $request->user();
                $identifier = $user?->id ?? $session->getId();

                // Extract success message
                $successMsg = $session->get('success') ?? $session->get('status');

                // Extract error message
                $errorMsg = $session->get('error');
                if (! $errorMsg && $session->has('errors')) {
                    $eb = $session->get('errors');
                    if ($eb instanceof ViewErrorBag && $eb->any()) {
                        $errorMsg = $eb->first();
                    }
                }

                // If response is a RedirectResponse, also check its internal session store directly
                if ($response instanceof RedirectResponse) {
                    $respSession = $response->getSession();
                    if ($respSession) {
                        $successMsg = $successMsg ?? $respSession->get('success') ?? $respSession->get('status');
                        if (! $errorMsg) {
                            $errorMsg = $respSession->get('error');
                            if (! $errorMsg && $respSession->has('errors')) {
                                $eb = $respSession->get('errors');
                                if ($eb instanceof ViewErrorBag && $eb->any()) {
                                    $errorMsg = $eb->first();
                                }
                            }
                        }
                    }
                }

                // Persist to bulletproof cache store for traditional full-page redirects.
                // For in-app AJAX actions (isAction), the flash is delivered directly in the JSON payload!
                if (! $isAction) {
                    if ($successMsg) {
                        app(FlashNotificationService::class)->store($identifier, 'success', (string) $successMsg);
                        $session->put('tbs_flash_success', $successMsg);
                        $session->put('tbs_flash_time', now()->timestamp);
                    } elseif ($errorMsg) {
                        app(FlashNotificationService::class)->store($identifier, 'error', (string) $errorMsg);
                        $session->put('tbs_flash_error', $errorMsg);
                        $session->put('tbs_flash_time', now()->timestamp);
                    }
                } else {
                    $session->forget(['tbs_flash_success', 'tbs_flash_error', 'tbs_flash_time', 'success', 'error', 'status', 'errors']);
                }

                // If this action was dispatched via in-app AJAX (X-TBS-Action: true):
                if ($isAction) {
                    $targetUrl = $response instanceof RedirectResponse ? $response->getTargetUrl() : null;
                    $referer = $request->header('referer');

                    $isNavigatingAway = false;
                    if ($targetUrl) {
                        $targetPath = parse_url($targetUrl, PHP_URL_PATH) ?? '';
                        $refererPath = $referer ? (parse_url($referer, PHP_URL_PATH) ?? '') : '';

                        if (str_contains($targetPath, '/gevangenis') || str_contains($targetPath, '/login') || str_contains($targetPath, '/battle/arena')) {
                            $isNavigatingAway = true;
                        } elseif ($refererPath && $targetPath !== $refererPath && ! empty($targetPath) && $targetPath !== '/') {
                            $isNavigatingAway = true;
                        }
                    }

                    if ($isNavigatingAway && $targetUrl) {
                        return response()->json([
                            'success' => ! (bool) $errorMsg,
                            'redirect' => $targetUrl,
                            'flash' => [
                                'type' => $errorMsg ? 'error' : 'success',
                                'message' => $errorMsg ?? $successMsg,
                            ],
                        ]);
                    }

                    // Otherwise, return JSON with fresh character stats and flash message for in-app smooth updates
                    $character = $user?->character?->fresh();
                    if ($character) {
                        try {
                            app(EnergyService::class)->refresh($character);
                            $reqXp = app(LevelService::class)->getXpRequiredForNextLevel($character->level);
                        } catch (\Throwable $e) {
                            $reqXp = 100;
                        }
                    }

                    $charData = null;
                    if ($character) {
                        $charData = [
                            'cash' => (int) $character->cash,
                            'cashFormatted' => '€'.number_format($character->cash, 0, ',', '.'),
                            'cash_formatted' => '€'.number_format($character->cash, 0, ',', '.'),
                            'bank' => (int) $character->bank,
                            'bankFormatted' => '€'.number_format($character->bank, 0, ',', '.'),
                            'bank_formatted' => '€'.number_format($character->bank, 0, ',', '.'),
                            'total' => (int) ($character->cash + $character->bank),
                            'totalFormatted' => '€'.number_format($character->cash + $character->bank, 0, ',', '.'),
                            'total_formatted' => '€'.number_format($character->cash + $character->bank, 0, ',', '.'),
                            'energy' => (int) $character->energy,
                            'max_energy' => (int) $character->max_energy,
                            'energy_pct' => min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))),
                            'energy_percent' => min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))),
                            'health' => (int) $character->health,
                            'max_health' => (int) $character->max_health,
                            'health_pct' => min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))),
                            'health_percent' => min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))),
                            'xp' => (int) $character->xp,
                            'req_xp' => (int) ($reqXp ?? 100),
                            'level' => (int) $character->level,
                            'stars' => (int) ($character->stars ?? 0),
                            'vip_points' => (int) ($character->vip_points ?? 0),
                            'respect' => (string) $character->formatted_respect,
                        ];
                    }

                    return response()->json([
                        'success' => ! (bool) $errorMsg,
                        'same_page' => true,
                        'redirect_url' => $targetUrl,
                        'flash' => [
                            'type' => $errorMsg ? 'error' : 'success',
                            'message' => $errorMsg ?? $successMsg,
                        ],
                        'character' => $charData,
                    ]);
                }
            }

            // On AJAX / polling requests, ensure flash data stays alive for the subsequent HTML page load
            if ($isAjaxOrPolling && ! $response->isRedirection() && ! $isAction) {
                $session->reflash();
                $session->keep(['success', 'error', 'status', 'warning', 'info']);
            }
        }

        if ($isBackgroundRefresh) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
