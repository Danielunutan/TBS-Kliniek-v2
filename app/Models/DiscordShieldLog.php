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
        return static::create([
            'event' => $event,
            'description' => $description,
            'discord_id' => $data['discord_id'] ?? null,
            'username' => $data['username'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'fingerprint_hash' => $data['fingerprint_hash'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
