<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CinemaRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'host_character_id',
        'youtube_url',
        'youtube_video_id',
        'is_official',
        'is_private',
        'passcode',
        'views_count',
    ];

    protected $casts = [
        'is_official' => 'boolean',
        'is_private' => 'boolean',
        'views_count' => 'integer',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'host_character_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CinemaMessage::class, 'room_id')->latest()->take(50);
    }

    /**
     * Parse YouTube ID from various YouTube URL formats.
     */
    public static function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i',
            '/^[a-zA-Z0-9_-]{11}$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($url), $matches)) {
                return $matches[1] ?? $matches[0];
            }
        }

        return null;
    }
}
