<?php

namespace App\Services;

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

        // If no secret key is set yet, gracefully pass to allow testing until keys are added to .env
        if (empty($secret)) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip ?? request()->ip(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return (bool) ($data['success'] ?? false);
            }
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification failed: '.$e->getMessage());
        }

        return false;
    }
}
