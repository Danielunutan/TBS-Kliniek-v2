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
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'buyer_id');
    }
}
