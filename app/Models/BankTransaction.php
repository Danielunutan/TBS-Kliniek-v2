<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'character_id',
        'type',
        'source',
        'amount',
        'balance_after',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * The character who owns this transaction.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Record a new transaction quickly.
     */
    public static function record(
        Character $character,
        string $type,
        string $source,
        int $amount,
        int $balanceAfter,
        string $description
    ): self {
        return self::create([
            'character_id' => $character->id,
            'type' => $type,
            'source' => $source,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'description' => $description,
        ]);
    }
}
