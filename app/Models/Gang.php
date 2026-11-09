<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    public const MAX_NPC_VEHICLE_TUNING_PERCENT = 75;

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
        'protected_treasury' => 'integer',
        'is_open' => 'boolean',
        'last_attack_at' => 'datetime',
        'shield_until' => 'datetime',
        'vip_boost_until' => 'datetime',
        'war_dominance_until' => 'datetime',
        'leave_fee_multiplier' => 'integer',
        'min_respect' => 'integer',
        'masks' => 'integer',
        'has_gang_chat' => 'boolean',
        'leader_character_id' => 'integer',
        'personal_shopper' => 'integer',
        'money_magnet' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $gang) {
            if (isset($gang->protected_treasury) && (int) $gang->treasury < (int) $gang->protected_treasury) {
                $gang->protected_treasury = max(0, (int) $gang->treasury);
            }
        });
    }

    public function getLootableTreasuryAttribute(): int
    {
        $protected = max(0, (int) ($this->protected_treasury ?? 0));

        return max(0, (int) $this->treasury - $protected);
    }

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
        return $this->hasMany(GangNpc::class)->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function allNpcs(): HasMany
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

    public function activeWar(): ?GangWar
    {
        return GangWar::findActiveForGang($this->id);
    }

    public function hasWarDominance(): bool
    {
        if (! $this->war_dominance_until) {
            return false;
        }

        $dt = $this->war_dominance_until instanceof CarbonInterface
            ? $this->war_dominance_until
            : Carbon::parse($this->war_dominance_until);

        return $dt->isFuture();
    }

    public function getTotalStorageCapacityAttribute(): int
    {
        $propertyStorage = $this->relationLoaded('properties')
            ? (int) $this->properties->sum('storage_capacity')
            : (int) $this->properties()->sum('storage_capacity');

        return max(0, $propertyStorage + $this->npc_storage_boost);
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
        $propertyCap = $this->relationLoaded('properties')
            ? (int) $this->properties->sum('npc_capacity')
            : (int) $this->properties()->sum('npc_capacity');

        // Elke bende beschikt over een basiscapaciteit van minimaal 2 NPC-slots zodat ook kleine bendes specialisten kunnen rekruteren
        return max(2, $propertyCap);
    }

    public function getTotalRespectAttribute(): int
    {
        if (isset($this->attributes['respect']) && (int) $this->attributes['respect'] > 0) {
            return (int) $this->attributes['respect'];
        }

        if ($this->relationLoaded('members')) {
            return (int) $this->members->sum('respect');
        }

        return (int) $this->members()->sum('respect');
    }

    public function getNpcDefenseBonusPercentAttribute(): int
    {
        $defenseNpcs = $this->relationLoaded('npcs')
            ? $this->npcs->filter(fn ($n) => ! $n->isExpired())->where('bonus_type', 'defense')
            : $this->npcs()->where('bonus_type', 'defense')->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->get();

        $sum = 0;
        $counts = [];
        foreach ($defenseNpcs as $npc) {
            if ($npc->isExpired()) {
                continue;
            }
            $key = (string) $npc->npc_key;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
            if ($counts[$key] <= GangNpc::MAX_ACTIVE_PER_TYPE) {
                $sum += max(0, (int) $npc->bonus_value);
            }
        }

        return max(0, min(self::MAX_NPC_DEFENSE_BONUS_PERCENT, $sum));
    }

    /**
     * Sum extra gang depot storage slots from active storage_boost NPCs.
     */
    public function getNpcStorageBoostAttribute(): int
    {
        $storageNpcs = $this->relationLoaded('npcs')
            ? $this->npcs->filter(fn ($n) => ! $n->isExpired())->where('bonus_type', 'storage_boost')
            : $this->npcs()->where('bonus_type', 'storage_boost')->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->get();

        $sum = 0;
        $counts = [];
        foreach ($storageNpcs as $npc) {
            if ($npc->isExpired()) {
                continue;
            }
            $key = (string) $npc->npc_key;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
            if ($counts[$key] <= GangNpc::MAX_ACTIVE_PER_TYPE) {
                $sum += max(0, (int) $npc->bonus_value);
            }
        }

        return max(0, $sum);
    }

    /**
     * Sum gang garage repair discount percentage from active vehicle_tuning NPCs.
     */
    public function getNpcVehicleTuningPercentAttribute(): int
    {
        $tuningNpcs = $this->relationLoaded('npcs')
            ? $this->npcs->filter(fn ($n) => ! $n->isExpired())->where('bonus_type', 'vehicle_tuning')
            : $this->npcs()->where('bonus_type', 'vehicle_tuning')->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->get();

        $sum = 0;
        $counts = [];
        foreach ($tuningNpcs as $npc) {
            if ($npc->isExpired()) {
                continue;
            }
            $key = (string) $npc->npc_key;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
            if ($counts[$key] <= GangNpc::MAX_ACTIVE_PER_TYPE) {
                $sum += max(0, (int) $npc->bonus_value);
            }
        }

        return max(0, min(self::MAX_NPC_VEHICLE_TUNING_PERCENT, $sum));
    }

    public function getTotalDefenseAttribute(): int
    {
        $baseDefense = max(500, (int) ($this->protection_points ?? 1000));
        $bonusPct = max(0, min(self::MAX_NPC_DEFENSE_BONUS_PERCENT, $this->npc_defense_bonus_percent));
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
