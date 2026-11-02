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
 * @property int $charisma_bonus
 * @property int $discipline_bonus
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

    /**
     * Calculate the cash resale value per unit in the Depot (60% of the base shop price).
     */
    public function getDepotSellPrice(): int
    {
        $slug = (string) $this->slug;
        $isEventItem = str_starts_with($slug, 'halloween_') || str_starts_with($slug, 'autumn_');

        $basePrice = $this->price_kc > 0
            ? $this->price_kc
            : ($isEventItem ? 2500 : 1000);

        return max(1, (int) round($basePrice * 0.6));
    }

    /**
     * Check if this item can be consumed from inventory for an active effect.
     */
    public function isConsumableItem(): bool
    {
        if (in_array($this->category, ['equipment', 'special'], true)) {
            return false;
        }

        if (in_array($this->slug, [
            'sigaretten',
            'ritalin',
            'telefoon',
            'strip-oxazepam',
            'strip-ritalin',
            'haloperidol-forte',
            'adrenaline-injectie',
            'ehbo-noodverbanddoos',
            'eiwitshake-bodybuilder',
            'multivitaminen-focus',
            'bivakmuts-masker',
            'smokkelpakketje',
            'ijzerzaagje',
            'halloween_candy_bag',
            'autumn_spiced_cider',
        ], true)) {
            return true;
        }

        return ($this->energy_restore ?? 0) > 0 || ($this->health_restore ?? 0) > 0;
    }
}
