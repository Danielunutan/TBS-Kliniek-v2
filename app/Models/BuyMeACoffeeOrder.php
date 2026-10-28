<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $event
 * @property string|null $external_id
 * @property string|null $supporter_name
 * @property string|null $supporter_email
 * @property float $amount
 * @property string $currency
 * @property string|null $membership_tier
 * @property string|null $message
 * @property int|null $character_id
 * @property string $status
 * @property bool $is_membership
 * @property string|null $membership_status
 * @property array|null $payload
 * @property Carbon|null $linked_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Character|null $character
 */
class BuyMeACoffeeOrder extends Model
{
    use HasFactory;

    protected $table = 'buymeacoffee_orders';

    protected $fillable = [
        'event',
        'external_id',
        'supporter_name',
        'supporter_email',
        'amount',
        'currency',
        'membership_tier',
        'message',
        'character_id',
        'status',
        'is_membership',
        'membership_status',
        'payload',
        'linked_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_membership' => 'boolean',
        'payload' => 'array',
        'linked_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Character this purchase or membership belongs to.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Check if this order is linked to a character.
     */
    public function isLinked(): bool
    {
        return $this->character_id !== null;
    }

    /**
     * Check if membership is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->membership_status === 'cancelled';
    }

    /**
     * Formatted price label.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '€'.number_format((float) $this->amount, 2, ',', '.');
    }

    /**
     * Check if this order is a single/bulk coffee donation instead of recurring VIP.
     */
    public function getIsCoffeeDonationAttribute(): bool
    {
        $tier = strtolower((string) $this->membership_tier);

        return str_contains($tier, 'koffie') || str_contains($tier, 'bakkie') || ! $this->is_membership;
    }

    /**
     * Human readable display label for membership tier or donation.
     */
    public function getDisplayTierAttribute(): string
    {
        if ($this->membership_tier) {
            return $this->membership_tier;
        }

        if ($this->is_membership) {
            return 'TBS V.I.P. Lid';
        }

        return 'Losse Koffie Donatie';
    }

    /**
     * Human readable summary of the granted rewards.
     */
    public function getRewardsSummaryAttribute(): string
    {
        $tier = (string) $this->membership_tier;
        $tierLower = strtolower($tier);

        if ($this->is_coffee_donation) {
            $amt = (float) $this->amount;
            if (str_contains($tier, '5x') || $amt >= 15.0) {
                return '+160 Sterren ⭐, +6 Maskers 🎭, +2 XXL Koffers 🧰, +500 Kogels 🔫';
            }

            if (str_contains($tier, '3x') || $amt >= 9.0) {
                return '+85 Sterren ⭐, +3 Maskers 🎭, +1 XXL Koffer 🧰, +200 Kogels 🔫';
            }

            return '+30 Sterren ⭐, +1 Masker 🎭, +50 Kogels 🔫';
        }

        if (str_contains($tierLower, 'lifetime')) {
            return '👑 VIP LIFETIME (99j), +1.500 Sterren ⭐, +30 Maskers 🎭, +10 XXL 🧰, +€50.000/u';
        }

        if (str_contains($tierLower, 'baas')) {
            return '👑 TBS Kliniek Baas (31d), +600 Sterren ⭐, +12 Maskers 🎭, +3 XXL 🧰, +€20.000/u';
        }

        if (str_contains($tierLower, 'gangster') || str_contains($tierLower, 'elite')) {
            return '👑 Gangster Elite (31d), +250 Sterren ⭐, +5 Maskers 🎭, +1 XXL 🧰, +€7.500/u';
        }

        return '👑 TBS V.I.P. Lid (31d), +100 Sterren ⭐, +2 Maskers 🎭, +€2.500/u';
    }
}
