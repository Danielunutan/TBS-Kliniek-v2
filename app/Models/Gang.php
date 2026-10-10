<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Gang
 *
 * @property int $id
 * @property string $name
 * @property string $tag
 * @property int|null $leader_character_id
 * @property string|null $description
 * @property string $specialty
 * @property string $emblem
 * @property int $treasury
 */
class Gang extends Model
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
        'treasury' => 'integer',
        'is_open' => 'boolean',
        'last_attack_at' => 'datetime',
        'shield_until' => 'datetime',
        'leave_fee_multiplier' => 'integer',
        'min_respect' => 'integer',
    ];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }

        return asset('assets/logo.png');
    }

    public function getBannerUrlAttribute(): string
    {
        if ($this->banner) {
            return asset($this->banner);
        }

        return asset('assets/locations/binnenplaats.jpg');
    }

    /**
     * Get the leader of the gang.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'leader_character_id');
    }

    public function personalShopper(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'personal_shopper');
    }

    public function moneyMagnet(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'money_magnet');
    }

    /**
     * Get the members of the gang.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(GangRequest::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(GangDonation::class);
    }

    public function alliances(): HasMany
    {
        return $this->hasMany(GangAlliance::class);
    }

    public function attacksSent(): HasMany
    {
        return $this->hasMany(GangAttack::class, 'attacker_gang_id');
    }

    public function attacksReceived(): HasMany
    {
        return $this->hasMany(GangAttack::class, 'target_gang_id');
    }

    public function isUnderShield(): bool
    {
        return $this->shield_until !== null && $this->shield_until->isFuture();
    }

    public function shieldSecondsRemaining(): int
    {
        if (! $this->isUnderShield()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->shield_until, false));
    }

    public function attackCooldownRemaining(): int
    {
        if (! $this->last_attack_at) {
            return 0;
        }

        $cooldownEnds = $this->last_attack_at->copy()->addMinutes(10);
        if ($cooldownEnds->isPast()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($cooldownEnds, false));
    }
}
