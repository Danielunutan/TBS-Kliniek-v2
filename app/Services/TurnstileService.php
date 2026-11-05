<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    /**
     * Verify a Cloudflare Turnstile response token.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret_key');

        if (! is_string($secret) || trim($secret) === '') {
            Log::warning('Turnstile verification unavailable: secret key is not configured.');

            return false;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->retry(2, 150, fn (\Throwable $exception): bool => $exception instanceof ConnectionException, throw: false)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip ?? request()->ip(),
                ]);

            if ($response->successful()) {
                return $response->json('success') === true;
            }
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification failed: '.$e->getMessage());
        }

        return false;
    }
}
