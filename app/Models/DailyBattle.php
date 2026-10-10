<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyBattle extends Model
{
    use HasFactory;

    protected $fillable = [
        'battle_date',
        'title',
        'is_settled',
        'settled_at',
        'winners_data',
    ];

    protected $casts = [
        'battle_date' => 'string',
        'is_settled' => 'boolean',
        'settled_at' => 'datetime',
        'winners_data' => 'array',
    ];

    public function scores(): HasMany
    {
        return $this->hasMany(DailyBattleScore::class, 'battle_date', 'battle_date');
    }
}
