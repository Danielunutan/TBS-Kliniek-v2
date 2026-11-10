<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'gang_id',
        'character_id',
        'invited_by_id',
        'message',
        'status',
    ];

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'invited_by_id');
    }
}
