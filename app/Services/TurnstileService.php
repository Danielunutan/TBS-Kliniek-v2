<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TurnstileService
{
    public const MAX_TOKEN_LENGTH = 2048;

    public const MAX_TOKEN_AGE_SECONDS = 300;

    /**
     * Generate an HMAC-bound cData token for Cloudflare Turnstile widget binding.
     */
    public static function generateCdata(string $scope, int|string $subjectId): string
    {
        $cleanScope = preg_replace('/[^A-Za-z0-9_-]/', '', $scope) ?: 'tbs';
        $cleanSubject = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $subjectId) ?: '0';
        $appKey = (string) config('app.key', 'tbs-turnstile-secret');
        $mac = substr(hash_hmac('sha256', "{$cleanScope}:{$cleanSubject}", $appKey), 0, 24);

        return substr("{$cleanScope}_{$cleanSubject}_{$mac}", 0, 64);
    }

    /**
     * Verify a Cloudflare Turnstile response token with hard-mode anti-bot checks.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        $context = [];
        try {
            if (app()->bound('request')) {
                $reqContext = request()->attributes->get('turnstile_context', []);
                if (is_array($reqContext)) {
                    $context = $reqContext;
                }
            }
        } catch (\Throwable) {
            $context = [];
        }

        $secret = $context['secret'] ?? config('services.turnstile.secret_key');

        if (! is_string($secret) || trim($secret) === '') {
            Log::warning('Turnstile verification unavailable: secret key is not configured.');

            return false;
        }

        if (! is_string($token)) {
            return false;
        }

        $trimmedToken = trim($token);
        if (
            $trimmedToken === ''
            || strlen($trimmedToken) > self::MAX_TOKEN_LENGTH
            || preg_match('/\s/', $trimmedToken) === 1
        ) {
            return false;
        }

        $replayKey = 'turnstile_used_token:'.hash('sha256', $trimmedToken);
        if (! Cache::add($replayKey, true, now()->addMinutes(10))) {
            Log::warning('Turnstile verification rejected: single-use token replay detected.');

            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->retry(2, 150, fn (\Throwable $exception): bool => $exception instanceof ConnectionException, throw: false)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $trimmedToken,
                    'remoteip' => $ip ?? request()->ip(),
                    'idempotency_key' => (string) Str::uuid(),
                ]);

            if (! $response->successful() || $response->json('success') !== true) {
                if ($response->json('error-codes')) {
                    Log::warning('Turnstile verification rejected by Cloudflare.', [
                        'error_codes' => $response->json('error-codes'),
                    ]);
                }

                return false;
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                return false;
            }

            $expectedAction = isset($context['action']) && is_string($context['action']) ? trim($context['action']) : '';
            $actualAction = isset($payload['action']) && is_string($payload['action']) ? trim($payload['action']) : '';
            if ($expectedAction !== '' && ! hash_equals($expectedAction, $actualAction)) {
                Log::warning('Turnstile verification rejected: action mismatch.', [
                    'expected' => $expectedAction,
                    'actual' => $actualAction,
                ]);

                return false;
            }

            $expectedCdata = isset($context['cdata']) && is_string($context['cdata']) ? trim($context['cdata']) : '';
            $actualCdata = isset($payload['cdata']) && is_string($payload['cdata']) ? trim($payload['cdata']) : '';
            if ($expectedCdata !== '' && ! hash_equals($expectedCdata, $actualCdata)) {
                Log::warning('Turnstile verification rejected: cData binding mismatch.');

                return false;
            }

            $expectedHostname = isset($context['hostname']) && is_string($context['hostname'])
                ? trim($context['hostname'])
                : trim((string) config('services.turnstile.expected_hostname', ''));
            $actualHostname = isset($payload['hostname']) && is_string($payload['hostname']) ? trim($payload['hostname']) : '';
            if ($expectedHostname !== '' && ! hash_equals(strtolower($expectedHostname), strtolower($actualHostname))) {
                Log::warning('Turnstile verification rejected: hostname mismatch.', [
                    'expected' => $expectedHostname,
                    'actual' => $actualHostname,
                ]);

                return false;
            }

            if (isset($payload['challenge_ts']) && is_string($payload['challenge_ts']) && trim($payload['challenge_ts']) !== '') {
                try {
                    $challengeTime = Carbon::parse($payload['challenge_ts']);
                    $ageSeconds = $challengeTime->diffInSeconds(now(), false);
                    if ($ageSeconds > self::MAX_TOKEN_AGE_SECONDS || $ageSeconds < -60) {
                        Log::warning('Turnstile verification rejected: expired or out-of-bounds challenge_ts.', [
                            'age_seconds' => $ageSeconds,
                        ]);

                        return false;
                    }
                } catch (\Throwable) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            Cache::forget($replayKey);
            Log::warning('Turnstile verification failed: '.$e->getMessage());
        }

        return false;
    }
}
