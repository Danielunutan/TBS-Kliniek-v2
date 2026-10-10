<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'sender_id');
    }
}
