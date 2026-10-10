<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterCooldown extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'action',
        'cooldown_until',
    ];

    protected $casts = [
        'cooldown_until' => 'datetime',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
