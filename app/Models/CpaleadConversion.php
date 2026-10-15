<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpaleadConversion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payout' => 'decimal:4',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
