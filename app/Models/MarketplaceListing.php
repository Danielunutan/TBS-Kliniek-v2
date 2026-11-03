<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceListing extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'integer',
        'price' => 'integer',
        'is_sold' => 'boolean',
        'metadata' => 'array',
        'related_id' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'buyer_id');
    }

    public function isDepotItem(): bool
    {
        return $this->item_type === 'depot_item';
    }

    public function isVehicle(): bool
    {
        return $this->item_type === 'vehicle';
    }

    public function isProperty(): bool
    {
        return $this->item_type === 'property';
    }

    /**
     * Get clean badge information for the listing.
     *
     * @return array{label: string, class: string, icon: string}
     */
    public function getTypeBadge(): array
    {
        return match ($this->item_type) {
            'depot_item' => [
                'label' => 'Depot Item',
                'class' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                'icon' => 'fa-box-archive',
            ],
            'vehicle' => [
                'label' => 'Garage Voertuig',
                'class' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'icon' => 'fa-car-side',
            ],
            'property' => [
                'label' => 'Bende Pand',
                'class' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'icon' => 'fa-building',
            ],
            'stars' => [
                'label' => 'Sterren',
                'class' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'icon' => 'fa-star',
            ],
            'packages' => [
                'label' => 'Smokkelkoffer',
                'class' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                'icon' => 'fa-suitcase-rolling',
            ],
            'masks' => [
                'label' => 'Masker',
                'class' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'icon' => 'fa-mask',
            ],
            'vault_codes' => [
                'label' => 'Kluiscode',
                'class' => 'bg-violet-50 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300 border-violet-200 dark:border-violet-800',
                'icon' => 'fa-key',
            ],
            'cash' => [
                'label' => 'Contant',
                'class' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                'icon' => 'fa-coins',
            ],
            'pumpkin_seeds' => [
                'label' => 'Gouden Eikels',
                'class' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'icon' => 'fa-seedling',
            ],
            default => [
                'label' => ucfirst($this->item_type),
                'class' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                'icon' => 'fa-tag',
            ],
        };
    }
}
