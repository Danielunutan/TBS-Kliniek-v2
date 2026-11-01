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
        'video_started_at',
        'seek_offset',
        'video_queue',
        'is_official',
        'is_private',
        'is_closed',
        'closed_at',
        'passcode',
        'views_count',
    ];

    protected $casts = [
        'host_character_id' => 'integer',
        'video_started_at' => 'datetime',
        'seek_offset' => 'integer',
        'is_official' => 'boolean',
        'is_private' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
        'video_queue' => 'array',
        'views_count' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'passcode',
    ];

    /**
     * Get the current synchronized playback position in seconds.
     */
    public function getCurrentPlaybackSeconds(): int
    {
        if (! $this->video_started_at) {
            return 0;
        }

        $startedTimestamp = $this->video_started_at instanceof \DateTimeInterface
            ? $this->video_started_at->getTimestamp()
            : strtotime((string) $this->video_started_at);

        $elapsed = max(0, time() - $startedTimestamp);

        return $elapsed + (int) ($this->seek_offset ?? 0);
    }

    /**
     * Get the YouTube thumbnail URL.
     */
    public function getThumbnailUrl(): string
    {
        return $this->youtube_video_id
            ? "https://img.youtube.com/vi/{$this->youtube_video_id}/mqdefault.jpg"
            : asset('images/cinema-placeholder.jpg');
    }

    /**
     * Get current video queue safely as an array.
     */
    public function getQueue(): array
    {
        return is_array($this->video_queue) ? $this->video_queue : [];
    }

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
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i',
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
