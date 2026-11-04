<?php

namespace App\Support;

/**
 * Restrict outbound push requests to HTTPS endpoints operated by browser push services.
 */
final class PushEndpoint
{
    private const ALLOWED_HOSTS = [
        'fcm.googleapis.com',
        'updates.push.services.mozilla.com',
    ];

    public static function isAllowed(string $endpoint): bool
    {
        if (strlen($endpoint) > 4096 || filter_var($endpoint, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($endpoint);
        if (! is_array($parts)
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)
        ) {
            return false;
        }

        $host = rtrim(strtolower($parts['host'] ?? ''), '.');
        $host = trim($host, '[]');

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        return in_array($host, self::ALLOWED_HOSTS, true)
            || str_ends_with($host, '.fcm.googleapis.com')
            || str_ends_with($host, '.push.services.mozilla.com')
            || str_ends_with($host, '.push.apple.com')
            || str_ends_with($host, '.notify.windows.com');
    }
}
