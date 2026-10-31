<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordVerification extends Model
{
    protected $fillable = [
        'discord_id',
        'username',
        'discriminator',
        'avatar',
        'email',
        'character_id',
        'user_id',
        'ip_address',
        'country_code',
        'is_vpn',
        'vpn_provider',
        'fingerprint_hash',
        'fingerprint_raw',
        'device_type',
        'browser',
        'os',
        'status',
        'alt_of_discord_id',
        'alt_of_username',
        'risk_score',
        'turnstile_passed',
        'verified_at',
    ];

    protected $casts = [
        'fingerprint_raw' => 'array',
        'is_vpn' => 'boolean',
        'turnstile_passed' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get avatar url or fallback to Discord default.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return "https://cdn.discordapp.com/avatars/{$this->discord_id}/{$this->avatar}.png?size=128";
        }

        return 'https://cdn.discordapp.com/embed/avatars/0.png';
    }

    /**
     * Other accounts sharing the same fingerprint hash.
     */
    public function getLinkedAccountsAttribute()
    {
        if (empty($this->fingerprint_hash)) {
            return collect();
        }

        return static::where('fingerprint_hash', $this->fingerprint_hash)
            ->where('discord_id', '!=', $this->discord_id)
            ->orderByDesc('id')
            ->get();
    }
}
