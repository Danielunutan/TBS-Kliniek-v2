<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'friend_id',
        'is_bff',
        'last_respected_at',
        'status',
    ];

    protected $casts = [
        'is_bff' => 'boolean',
        'last_respected_at' => 'datetime',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'friend_id');
    }
}
