<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordShieldSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting by key, with optional fallback or default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if ($setting && $setting->value !== null && $setting->value !== '') {
            return $setting->value;
        }

        // Standard defaults
        $defaults = [
            'bot_token' => env('DISCORD_BOT_TOKEN', ''),
            'client_id' => env('DISCORD_CLIENT_ID', '1558628995439005748'),
            'client_secret' => env('DISCORD_CLIENT_SECRET', 'tvX_NGRzmXI4fwt4HJGrl7Lc6cUNjBo9'),
            'guild_id' => env('DISCORD_GUILD_ID', ''),
            'verified_role_id' => env('DISCORD_VERIFIED_ROLE_ID', ''),
            'unverified_role_id' => env('DISCORD_UNVERIFIED_ROLE_ID', ''),
            'log_channel_id' => env('DISCORD_LOG_CHANNEL_ID', ''),
            'block_vpns' => '1',
            'block_alts' => '1',
            'auto_kick_alts' => '1',
            'auto_ban_evaders' => '0',
            'turnstile_enabled' => '1',
            'require_game_account' => '0',
        ];

        return $default ?? ($defaults[$key] ?? null);
    }

    /**
     * Set a setting by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );
    }
}
