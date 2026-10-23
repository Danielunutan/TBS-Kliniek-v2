<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->hasSession() ? $request->session() : null;

        $isAjaxOrPolling = $request->ajax()
            || $request->expectsJson()
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
        if ($session && $isAjaxOrPolling) {
            $session->reflash();
            $session->keep(['success', 'error', 'status', 'warning', 'info']);
        }

        $response = $next($request);

        // 2. After response:
        if ($session) {
            // Mirror newly set flash messages into our resilient persistent session keys on redirects
            if ($response->isRedirection()) {
                if ($session->has('success')) {
                    $session->put('tbs_flash_success', $session->get('success'));
                    $session->put('tbs_flash_time', now()->timestamp);
                }
                if ($session->has('status')) {
                    $session->put('tbs_flash_success', $session->get('status'));
                    $session->put('tbs_flash_time', now()->timestamp);
                }
                if ($session->has('error')) {
                    $session->put('tbs_flash_error', $session->get('error'));
                    $session->put('tbs_flash_time', now()->timestamp);
                }
            }

            // On AJAX / polling requests, ensure flash data stays alive for the subsequent HTML page load
            if ($isAjaxOrPolling && ! $response->isRedirection()) {
                $session->reflash();
                $session->keep(['success', 'error', 'status', 'warning', 'info']);
            }
        }

        return $response;
    }
}
