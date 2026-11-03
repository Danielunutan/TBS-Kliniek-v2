<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordShieldLog extends Model
{
    protected $fillable = [
        'discord_id',
        'username',
        'event',
        'description',
        'ip_address',
        'fingerprint_hash',
        'severity',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Quickly create a new log entry.
     */
    public static function log(string $event, string $description, array $data = []): static
    {
        $isStaffAction = in_array($event, ['manual_approval', 'manual_ban', 'manual_unban', 'settings_updated'], true);
        $ipAddress = array_key_exists('ip_address', $data)
            ? $data['ip_address']
            : ($isStaffAction ? null : request()->ip());

        if ($ipAddress !== null && ! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $ipAddress = null;
        }

        return static::create([
            'event' => $event,
            'description' => $description,
            'discord_id' => $data['discord_id'] ?? null,
            'username' => $data['username'] ?? null,
            'ip_address' => $ipAddress,
            'fingerprint_hash' => $data['fingerprint_hash'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
