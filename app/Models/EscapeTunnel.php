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
        'fugitive_until' => 'datetime',
        'times_escaped' => 'integer',
    ];

    public const FUGITIVE_DURATION_MINUTES = 15;

    public const ESCAPE_HEISTS = [
        'safehouse' => [
            'id' => 'safehouse',
            'name' => 'Onderduikers Safehouse Kraak',
            'desc' => 'Breek in bij een verlaten onderduikadres in het sparrenbos en verzamel cash en proviand.',
            'energy' => 15,
            'cooldown' => 600,
            'base_chance' => 100,
            'fail_jail_secs' => 90,
            'min_cash' => 5000,
            'max_cash' => 12500,
            'xp' => 120,
            'respect' => 5,
            'min_stars' => 0,
            'max_stars' => 1,
            'icon' => 'fa-house-chimney-crack',
            'color' => 'amber',
        ],
        'villa' => [
            'id' => 'villa',
            'name' => 'Kliniekdirecteur Residentie Inbraak',
            'desc' => 'Sluip de directiewoning binnen en kraak de kluis in het privékantoor voor dossiers en buit.',
            'energy' => 25,
            'cooldown' => 1200,
            'base_chance' => 75,
            'fail_jail_secs' => 150,
            'min_cash' => 20000,
            'max_cash' => 45000,
            'xp' => 250,
            'respect' => 10,
            'min_stars' => 1,
            'max_stars' => 2,
            'icon' => 'fa-building-shield',
            'color' => 'purple',
        ],
        'transport' => [
            'id' => 'transport',
            'name' => 'Geldtransport Onderschepping',
            'desc' => 'Onderschep een waardetransport over de provinciale bosweg voor maximale buit!',
            'energy' => 40,
            'cooldown' => 1800,
            'base_chance' => 60,
            'fail_jail_secs' => 240,
            'min_cash' => 50000,
            'max_cash' => 100000,
            'xp' => 450,
            'respect' => 20,
            'min_stars' => 2,
            'max_stars' => 5,
            'icon' => 'fa-truck-fast',
            'color' => 'emerald',
        ],
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

    public function isFugitive(): bool
    {
        return $this->fugitive_until !== null && $this->fugitive_until->isFuture();
    }

    public function remainingFugitiveSeconds(): int
    {
        if (! $this->isFugitive()) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->fugitive_until, false);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
