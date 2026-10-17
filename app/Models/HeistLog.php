<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeistLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'success' => 'boolean',
        'cash_earned' => 'integer',
        'gang_cash_earned' => 'integer',
        'xp_earned' => 'integer',
        'respect_earned' => 'integer',
        'condition_loss' => 'integer',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function heist(): BelongsTo
    {
        return $this->belongsTo(Heist::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }
}
