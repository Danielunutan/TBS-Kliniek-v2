<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyBattleScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'battle_date',
        'character_id',
        'score_slay',
        'score_hustles',
        'score_payments',
        'score_breakouts',
        'score_drugs_made',
        'score_drugs_sold',
        'score_kills',
    ];

    protected $casts = [
        'battle_date' => 'string',
        'score_slay' => 'integer',
        'score_hustles' => 'integer',
        'score_payments' => 'integer',
        'score_breakouts' => 'integer',
        'score_drugs_made' => 'integer',
        'score_drugs_sold' => 'integer',
        'score_kills' => 'integer',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
