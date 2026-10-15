<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileRespect extends Model
{
    use HasFactory;

    protected $fillable = [
        'giver_id',
        'receiver_id',
    ];

    public function giver(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'giver_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'receiver_id');
    }
}
