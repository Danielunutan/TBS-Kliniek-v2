<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a setting by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting || $setting->value === null) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    /**
     * Set a setting by key.
     */
    public static function set(string $key, mixed $value, ?string $description = null): static
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encoded,
                'description' => $description ?? $key,
            ]
        );
    }
}
