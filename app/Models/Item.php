<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Item
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $category
 * @property string $rarity
 * @property int $price_kc
 * @property int $energy_restore
 * @property int $health_restore
 * @property int $strength_bonus
 * @property int $defense_bonus
 * @property int $stamina_bonus
 * @property int $intelligence_bonus
 * @property string $icon
 * @property bool $is_buyable
 */
class Item extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price_kc' => 'integer',
        'energy_restore' => 'integer',
        'health_restore' => 'integer',
        'strength_bonus' => 'integer',
        'defense_bonus' => 'integer',
        'stamina_bonus' => 'integer',
        'intelligence_bonus' => 'integer',
        'charisma_bonus' => 'integer',
        'discipline_bonus' => 'integer',
        'is_buyable' => 'boolean',
    ];
}
