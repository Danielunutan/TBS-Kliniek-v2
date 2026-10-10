<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'gang_id',
        'character_id',
        'amount',
    ];

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
