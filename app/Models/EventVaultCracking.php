<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventVaultCracking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'character_id',
        'amount',
        'source',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the character that cracked the vault code.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
