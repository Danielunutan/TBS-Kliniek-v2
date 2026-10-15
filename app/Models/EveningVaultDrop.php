<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EveningVaultDrop extends Model
{
    protected $fillable = [
        'character_id',
        'drop_date',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
