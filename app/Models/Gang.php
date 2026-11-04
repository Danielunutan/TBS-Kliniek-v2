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

    public const MAX_NPC_DEFENSE_BONUS_PERCENT = 100;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'leave_fee_multiplier' => 0,
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'treasury' => 'integer',
        'is_open' => 'boolean',
        'last_attack_at' => 'datetime',
        'shield_until' => 'datetime',
        'vip_boost_until' => 'datetime',
        'leave_fee_multiplier' => 'integer',
        'min_respect' => 'integer',
        'masks' => 'integer',
        'has_gang_chat' => 'boolean',
        'leader_character_id' => 'integer',
        'personal_shopper' => 'integer',
        'money_magnet' => 'integer',
    ];

    public function hasActiveVipBoost(): bool
    {
        return $this->vip_boost_until !== null && $this->vip_boost_until->isFuture();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }

        return asset('assets/logo.png');
    }

    public function getAvatarUrl(): string
    {
        return $this->avatar_url;
    }

    public function getBannerUrlAttribute(): string
    {
        if ($this->banner) {
            return asset($this->banner);
        }

        return asset('assets/illustrations/banner_gangs.jpg');
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

    public function properties(): HasMany
    {
        return $this->hasMany(GangProperty::class);
    }

    public function npcs(): HasMany
    {
        return $this->hasMany(GangNpc::class);
    }

    public function storedVehicles(): HasMany
    {
        return $this->hasMany(CharacterVehicle::class)->where('in_gang_garage', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GangItem::class);
    }

    public function getTotalStorageCapacityAttribute(): int
    {
        if ($this->relationLoaded('properties')) {
            return (int) $this->properties->sum('storage_capacity');
        }

        return (int) $this->properties()->sum('storage_capacity');
    }

    public function getTotalVehicleSlotsAttribute(): int
    {
        if ($this->relationLoaded('properties')) {
            return (int) $this->properties->sum('vehicle_slots');
        }

        return (int) $this->properties()->sum('vehicle_slots');
    }

    public function getTotalNpcCapacityAttribute(): int
    {
        if ($this->relationLoaded('properties')) {
            return (int) $this->properties->sum('npc_capacity');
        }

        return (int) $this->properties()->sum('npc_capacity');
    }

    public function getNpcDefenseBonusPercentAttribute(): int
    {
        $sum = $this->relationLoaded('npcs')
            ? (int) $this->npcs->where('bonus_type', 'defense')->sum('bonus_value')
            : (int) $this->npcs()->where('bonus_type', 'defense')->sum('bonus_value');

        return min(self::MAX_NPC_DEFENSE_BONUS_PERCENT, $sum);
    }

    public function getTotalDefenseAttribute(): int
    {
        $baseDefense = max(500, (int) ($this->protection_points ?? 1000));
        $bonusPct = $this->npc_defense_bonus_percent;
        if ($bonusPct <= 0) {
            return $baseDefense;
        }

        return $baseDefense + (int) round($baseDefense * ($bonusPct / 100));
    }

    public function getStorageUsed(): int
    {
        $itemsCount = $this->relationLoaded('items')
            ? (int) $this->items->sum('quantity')
            : (int) $this->items()->sum('quantity');
        $masksCount = (int) ($this->masks ?? 0);

        return $itemsCount + $masksCount;
    }

    public function canAddStorage(int $amount = 1): bool
    {
        return ($this->getStorageUsed() + $amount) <= $this->total_storage_capacity;
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

        return max(0, (int) ceil(now()->diffInSeconds($this->shield_until, false)));
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

        return max(0, (int) ceil(now()->diffInSeconds($cooldownEnds, false)));
    }

    /**
     * Check if a character has management/admin rights in this gang (Leader, Co-leader, Kantine Smokkelaar, or Kasbeheerder).
     */
    public function isOfficer(?Character $character): bool
    {
        if (! $character || (int) $this->id !== (int) $character->gang_id) {
            return false;
        }

        return in_array($character->gang_role, ['leader', 'co_leader'], true)
            || (int) $this->leader_character_id === (int) $character->id
            || ((int) $this->personal_shopper === (int) $character->id)
            || ((int) $this->money_magnet === (int) $character->id);
    }

    /**
     * Check if this gang owns a specific property key.
     */
    public function hasProperty(string $propertyKey): bool
    {
        if ($this->relationLoaded('properties')) {
            return $this->properties->contains('property_key', $propertyKey);
        }

        return $this->properties()->where('property_key', $propertyKey)->exists();
    }

    /**
     * Get the vehicle wear reduction multiplier for gang heists.
     */
    public function getHeistWearMultiplier(): float
    {
        // Chop-Shop Garage provides 25% wear protection on bende heists
        if ($this->hasProperty('chop_shop_garage')) {
            return 0.75;
        }

        return 1.0;
    }
}
