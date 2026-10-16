<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price_cash' => 'integer',
        'price_stars' => 'integer',
        'speed_pct' => 'integer',
        'reliability_pct' => 'integer',
        'min_level' => 'integer',
        'is_available' => 'boolean',
    ];

    public function characterVehicles(): HasMany
    {
        return $this->hasMany(CharacterVehicle::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_stars > 0 && $this->price_cash > 0) {
            return '€'.number_format($this->price_cash, 0, ',', '.').' of '.$this->price_stars.' ⭐';
        }
        if ($this->price_stars > 0) {
            return $this->price_stars.' ⭐';
        }

        return '€'.number_format($this->price_cash, 0, ',', '.');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'auto' => 'Vluchtwagen',
            'motor' => 'Vluchtmotor',
            'boot' => 'Smokkelboot',
            'helikopter' => 'Helikopter',
            'vliegtuig' => 'Vliegtuig',
            default => 'Voertuig',
        };
    }
}
