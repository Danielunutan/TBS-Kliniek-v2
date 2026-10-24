<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FlashNotificationService
{
    /**
     * Store a flash alert for a user or session.
     */
    public function store(string|int|null $identifier, string $type, string $message): void
    {
        if (! $identifier || empty($message)) {
            return;
        }

        $key = $this->getCacheKey($identifier);

        Cache::put($key, [
            'type' => $type, // 'success' or 'error'
            'message' => $message,
            'time' => now()->timestamp,
        ], 120); // 2 minutes TTL
    }

    /**
     * Consume and remove the flash alert for a user or session.
     *
     * @return array{type: string, message: string, time: int}|null
     */
    public function consume(string|int|null $identifier): ?array
    {
        if (! $identifier) {
            return null;
        }

        $key = $this->getCacheKey($identifier);
        $alert = Cache::pull($key);

        if (! is_array($alert) || empty($alert['message'])) {
            return null;
        }

        // Only return if it's recent (within 90 seconds)
        if ((now()->timestamp - (int) ($alert['time'] ?? 0)) > 90) {
            return null;
        }

        return $alert;
    }

    /**
     * Peek at the alert without removing it.
     *
     * @return array{type: string, message: string, time: int}|null
     */
    public function peek(string|int|null $identifier): ?array
    {
        if (! $identifier) {
            return null;
        }

        $key = $this->getCacheKey($identifier);
        $alert = Cache::get($key);

        if (! is_array($alert) || empty($alert['message'])) {
            return null;
        }

        if ((now()->timestamp - (int) ($alert['time'] ?? 0)) > 90) {
            Cache::forget($key);

            return null;
        }

        return $alert;
    }

    /**
     * Clear the alert.
     */
    public function forget(string|int|null $identifier): void
    {
        if (! $identifier) {
            return;
        }

        Cache::forget($this->getCacheKey($identifier));
    }

    protected function getCacheKey(string|int $identifier): string
    {
        return 'tbs_resilient_flash_'.$identifier;
    }
}
