<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    /**
     * Default loan interest percentage (8.5%).
     */
    public const DEFAULT_INTEREST_RATE = 8.50;

    /**
     * Minimum loan amount that can be requested (€10.000).
     */
    public const MIN_LOAN_AMOUNT = 10000;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'character_id',
        'loan_number',
        'amount',
        'interest_rate',
        'interest_amount',
        'total_due',
        'total_paid',
        'remaining_due',
        'status',
        'started_at',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'interest_rate' => 'float',
        'interest_amount' => 'integer',
        'total_due' => 'integer',
        'total_paid' => 'integer',
        'remaining_due' => 'integer',
        'started_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Character relationship.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Scope for active (unpaid) loans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for paid loans.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Calculate repayment progress percentage (0 - 100%).
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_due <= 0) {
            return 100.0;
        }

        return min(100.0, round(($this->total_paid / $this->total_due) * 100, 1));
    }

    /**
     * Calculate the maximum borrowing capacity for a character based on level and respect.
     * Level 1 = €50.000, Level 5 = €500.000, Level 7 = €700.000, etc.
     */
    public static function calculateMaxCapacity(Character $character): int
    {
        $level = max(1, (int) $character->level);

        return $level * 50000;
    }

    /**
     * Get the total outstanding remaining debt across all active loans for a character.
     */
    public static function getOpenstaandeSchuld(Character $character): int
    {
        return (int) self::where('character_id', $character->id)
            ->where('status', 'active')
            ->sum('remaining_due');
    }

    /**
     * Get the remaining available borrow amount for a character.
     */
    public static function getBeschikbareLening(Character $character): int
    {
        $max = self::calculateMaxCapacity($character);
        $open = self::getOpenstaandeSchuld($character);

        return max(0, $max - $open);
    }
}
