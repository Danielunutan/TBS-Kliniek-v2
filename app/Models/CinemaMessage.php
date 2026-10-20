<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CinemaMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'character_id',
        'message',
        'gif_url',
        'sticker_key',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(CinemaRoom::class, 'room_id');
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
