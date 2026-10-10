<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KofferLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'loot_summary',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
