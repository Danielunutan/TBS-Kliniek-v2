<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'rewarded',
        'rewarded_at',
        'ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rewarded' => 'boolean',
            'rewarded_at' => 'datetime',
        ];
    }

    /**
     * The character who referred the player.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'referrer_id');
    }

    /**
     * The new character who was referred.
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'referred_id');
    }
}
