<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HackingLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'vip_bonus_applied' => 'boolean',
        'success' => 'boolean',
        'cash_earned' => 'integer',
        'xp_earned' => 'integer',
        'ports_scanned' => 'integer',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
