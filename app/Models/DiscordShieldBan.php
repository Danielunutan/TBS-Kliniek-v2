<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordShieldBan extends Model
{
    protected $fillable = [
        'type',
        'value',
        'reason',
        'banned_by',
    ];

    /**
     * Check if a value (discord_id, fingerprint, ip) is banned.
     */
    public static function isBanned(string $type, string $value): bool
    {
        return static::where('type', $type)->where('value', $value)->exists();
    }
}
