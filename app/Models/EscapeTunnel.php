<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscapeTunnel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'stage' => 'integer',
        'progress_pct' => 'integer',
        'sand_in_socks' => 'integer',
        'spoons_used' => 'integer',
        'wood_planks' => 'integer',
        'is_completed' => 'boolean',
        'escaped_at' => 'datetime',
    ];

    public const STAGES = [
        1 => [
            'name' => 'Celvloer uitbeitelen',
            'desc' => 'Breek door de betonnen vloer achter je celbed met een eetlepel.',
            'target_progress' => 20,
        ],
        2 => [
            'name' => 'Fundering omzeilen',
            'desc' => 'Graaf onder de zware stenen fundamenten van Paviljoen 7 door.',
            'target_progress' => 40,
        ],
        3 => [
            'name' => 'Rioolbuis doorkruisen',
            'desc' => 'Navigeer langs de stinkende afvoer en stut de tunnel met houten balken.',
            'target_progress' => 60,
        ],
        4 => [
            'name' => 'Onder het hek door',
            'desc' => 'Je hoort de surveillancehonden boven je kop blaffen. Blijf diep graven.',
            'target_progress' => 80,
        ],
        5 => [
            'name' => 'Buitenmuur bereiken',
            'desc' => 'De laatste meters naar de vrijheid in het sparrenbos buiten de kliniek!',
            'target_progress' => 100,
        ],
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
