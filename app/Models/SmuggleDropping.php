<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmuggleDropping extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'cash_gained' => 'integer',
        'xp_gained' => 'integer',
        'caught' => 'boolean',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
