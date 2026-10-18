<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HalloweenQuestProgress extends Model
{
    use HasFactory;

    protected $table = 'halloween_quest_progress';

    protected $guarded = ['id'];

    protected $casts = [
        'current_count' => 'integer',
        'target_count' => 'integer',
        'is_claimed' => 'boolean',
        'claimed_at' => 'datetime',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function isCompleted(): bool
    {
        return $this->current_count >= $this->target_count;
    }
}
