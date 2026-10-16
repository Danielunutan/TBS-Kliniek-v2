<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'timeline_post_id',
        'character_id',
        'emoji',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TimelinePost::class, 'timeline_post_id');
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
