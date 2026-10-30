<?php

namespace App\Models;

use App\Services\DailyBattleService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Support\ProfileStickerCatalog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Class Character
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $title
 * @property string $avatar
 * @property string|null $bio
 * @property string $status_message
 * @property int $level
 * @property int $xp
 * @property int $total_xp
 * @property int $cash
 * @property int $bank
 * @property int $energy
 * @property int $max_energy
 * @property Carbon $last_energy_refill_at
 * @property int $health
 * @property int $max_health
 * @property int $respect
 * @property int $strength
 * @property int $defense
 * @property int $stamina
 * @property int $intelligence
 * @property int $charisma
 * @property int $discipline
 * @property int $fights_won
 * @property int $fights_lost
 * @property int $thefts_succeeded
 * @property int $thefts_failed
 * @property int $missions_completed
 * @property int $prison_sentences
 * @property int $prison_escapes
 * @property bool $is_in_prison
 * @property Carbon|null $prison_until
 * @property string|null $prison_reason
 * @property int|null $gang_id
 * @property string $gang_role
 * @property Carbon|null $protection_until
 * @property Carbon|null $cash_protection_until
 * @property Carbon|null $last_active_at
 * @property Carbon|null $last_gang_wage_at
 * @property float $today_respect
 * @property string|null $today_respect_date
 * @property-read float $today_respect_score
 * @property-read string $formatted_respect
 * @property-read string $formatted_today_respect
 */
class Character extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_onboarded' => true,
        'profile_color' => 'purple',
        'current_location' => 'Paviljoen 1 (Gesloten)',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_energy_refill_at' => 'datetime',
        'last_gang_wage_at' => 'datetime',
        'last_allowance_at' => 'datetime',
        'prison_until' => 'datetime',
        'protection_until' => 'datetime',
        'cash_protection_until' => 'datetime',
        'last_active_at' => 'datetime',
        'is_in_prison' => 'boolean',
        'level' => 'integer',
        'xp' => 'integer',
        'total_xp' => 'integer',
        'cash' => 'integer',
        'bank' => 'integer',
        'energy' => 'integer',
        'max_energy' => 'integer',
        'health' => 'integer',
        'max_health' => 'integer',
        'vault_tokens' => 'integer',
        'stars' => 'integer',
        'unopened_bags' => 'integer',
        'xxl_cases' => 'integer',
        'daily_attacks_count' => 'integer',
        'daily_deaths_count' => 'integer',
        'daily_deaths_date' => 'string',
        'claimed_level_rewards' => 'array',
        'strength' => 'integer',
        'defense' => 'integer',
        'stamina' => 'integer',
        'intelligence' => 'integer',
        'charisma' => 'integer',
        'discipline' => 'integer',
        'vip_points' => 'integer',
        'is_vip' => 'boolean',
        'vip_expires_at' => 'datetime',
        'discord_reward_claimed_at' => 'datetime',
        'last_vip_reward_at' => 'datetime',
        'username_changed_at' => 'datetime',
        'today_respect' => 'decimal:2',
        'today_respect_date' => 'string',
        'last_daily_spin_at' => 'datetime',
        'daily_spins_today' => 'integer',
        'last_streak_claim_at' => 'datetime',
        'streak_days' => 'integer',
        'leave_until' => 'datetime',
        'sedated_until' => 'datetime',
        'last_treatment_at' => 'datetime',
        'treatment_score' => 'integer',
        'leave_trips_count' => 'integer',
        'treatment_history' => 'array',
        'ads_watched_today' => 'integer',
        'ads_watched_total' => 'integer',
        'ad_mask_progress' => 'integer',
        'ad_vault_progress' => 'integer',
        'ad_lottery_progress' => 'integer',
        'ad_bullets_progress' => 'integer',
        'ad_coma_progress' => 'integer',
        'ad_prison_progress' => 'integer',
        'prison_escape_attempted' => 'boolean',
        'ad_vest_progress' => 'integer',
        'ad_daily_data' => 'array',
        'perks' => 'array',
        'bullets' => 'integer',
        'vest_until' => 'datetime',
        'coma_until' => 'datetime',
        'kills_count' => 'integer',
        'deaths_count' => 'integer',
        'last_ad_watched_at' => 'datetime',
        'last_ad_date' => 'string',
        'birthdate' => 'date',
        'is_onboarded' => 'boolean',
        'pumpkin_seeds' => 'integer',
        'unlocked_borders' => 'array',
        'unlocked_stickers' => 'array',
        'unlocked_widgets' => 'array',
        'weather_lat' => 'decimal:4',
        'weather_lon' => 'decimal:4',
        'last_hack_at' => 'datetime',
        'hacks_completed_total' => 'integer',
    ];

    public ?int $unreadDirectMessagesCountValue = null;

    /**
     * List of dashboard widgets that are free for all patients.
     *
     * @return array<int, string>
     */
    public static function freeWidgets(): array
    {
        return [
            'quickToolbar',
            'attributes',
            'activities',
            'lottery',
            'referral',
            'loginBonus',
            'gang',
            'missions',
            'clinicNews',
        ];
    }

    /**
     * Check if a specific dashboard widget is unlocked for this character.
     */
    public function isWidgetUnlocked(string $widgetKey): bool
    {
        if (in_array($widgetKey, self::freeWidgets(), true)) {
            return true;
        }

        $unlocked = $this->unlocked_widgets ?? [];

        $aliases = [
            'tunnel' => 'peuken_market',
            'treatment' => 'security',
            'packages' => 'roulette',
        ];

        if (isset($aliases[$widgetKey]) && in_array($aliases[$widgetKey], $unlocked, true)) {
            return true;
        }

        return in_array($widgetKey, $unlocked, true);
    }

    /**
     * Get rich metadata for the currently equipped avatar border / frame.
     *
     * @return array{key: string, name: string, emoji: string, badge_title: string, price_seeds: int, badge: string, description: string, ring: string, border: string, glow: string, badge_gradient: string, preview_class: string}|null
     */
    public function getEquippedBorderInfo(): ?array
    {
        if (empty($this->equipped_border)) {
            return null;
        }

        $borders = HalloweenService::getBorders();

        return $borders[$this->equipped_border] ?? null;
    }

    /**
     * Get CSS classes for the currently equipped avatar border.
     */
    public function getAvatarBorderClasses(): string
    {
        return match ($this->equipped_border) {
            'pumpkin_glow' => 'ring-4 ring-orange-500 shadow-[0_0_22px_rgba(249,115,22,0.85)] border-amber-400',
            'vampire_blood' => 'ring-4 ring-rose-700 shadow-[0_0_22px_rgba(225,29,72,0.85)] border-rose-900',
            'phantom_mist' => 'ring-4 ring-purple-500 shadow-[0_0_25px_rgba(168,85,247,0.85)] border-purple-300',
            'gothic_king' => 'ring-4 ring-amber-400 shadow-[0_0_25px_rgba(251,191,36,0.9)] border-amber-200',
            default => '',
        };
    }

    /**
     * Check if character has purchased / unlocked a specific border.
     */
    public function hasBorder(string $borderKey): bool
    {
        $borders = $this->unlocked_borders ?? [];

        return in_array($borderKey, $borders, true);
    }

    /**
     * Unlock a specific avatar border for the character.
     */
    public function unlockBorder(string $borderKey): void
    {
        $borders = $this->unlocked_borders ?? [];
        if (! in_array($borderKey, $borders, true)) {
            $borders[] = $borderKey;
            $this->unlocked_borders = $borders;
        }
    }

    /**
     * Get active daily ad bonus progress & claim status for today.
     *
     * @return array{date: string, prison_released: bool, xp_progress: int, xp_claimed: bool, cash_progress: int, cash_claimed: bool, stars_progress: int, stars_claimed: bool}
     */
    public function getAdDailyData(): array
    {
        $data = $this->ad_daily_data ?? [];
        $today = now()->toDateString();
        $default = [
            'date' => $today,
            'prison_released' => false,
            'xp_progress' => 0,
            'xp_claimed' => false,
            'cash_progress' => 0,
            'cash_claimed' => false,
            'stars_progress' => 0,
            'stars_claimed' => false,
        ];

        if (($data['date'] ?? null) !== $today) {
            return $default;
        }

        return array_merge($default, $data);
    }

    /**
     * Check if character has an active perk (24-hour duration).
     */
    public function hasPerk(string $perk): bool
    {
        $perks = $this->perks ?? [];
        if (! is_array($perks)) {
            return false;
        }

        if (isset($perks[$perk])) {
            $val = $perks[$perk];
            if (is_numeric($val)) {
                return (int) $val > now()->timestamp;
            }
            if (is_string($val)) {
                try {
                    return Carbon::parse($val)->isFuture();
                } catch (\Throwable) {
                    return false;
                }
            }
            if (is_bool($val)) {
                return $val;
            }
        }

        // Support legacy flat array format: ['grotere_broekzakken']
        if (in_array($perk, $perks, true)) {
            return true;
        }

        return false;
    }

    /**
     * Get the expiration Carbon instance for an active perk.
     */
    public function getPerkExpiresAt(string $perk): ?Carbon
    {
        $perks = $this->perks ?? [];
        if (! is_array($perks) || ! isset($perks[$perk])) {
            return null;
        }

        $val = $perks[$perk];
        if (is_numeric($val)) {
            $dt = Carbon::createFromTimestamp((int) $val);

            return $dt->isFuture() ? $dt : null;
        }

        if (is_string($val)) {
            try {
                $dt = Carbon::parse($val);

                return $dt->isFuture() ? $dt : null;
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Get remaining active seconds for a perk.
     */
    public function getPerkRemainingSeconds(string $perk): int
    {
        $expiresAt = $this->getPerkExpiresAt($perk);
        if (! $expiresAt) {
            return 0;
        }

        return max(0, (int) round(now()->diffInSeconds($expiresAt, false)));
    }

    /**
     * Get remaining active hours for a perk (rounded up).
     */
    public function getPerkRemainingHours(string $perk): int
    {
        return (int) ceil($this->getPerkRemainingSeconds($perk) / 3600);
    }

    /**
     * Activate or extend a 24-hour perk for the character.
     */
    public function addPerk(string $perk, int $hours = 24): void
    {
        $perks = $this->perks ?? [];
        if (! is_array($perks)) {
            $perks = [];
        }

        // Remove legacy flat string entry if present
        $legacyKey = array_search($perk, $perks, true);
        if ($legacyKey !== false) {
            unset($perks[$legacyKey]);
        }

        $currentExpiry = $this->getPerkExpiresAt($perk);
        $base = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : now();
        $newExpiry = $base->copy()->addHours($hours);

        $perks[$perk] = $newExpiry->toIso8601String();
        $this->perks = $perks;
    }

    /**
     * Get display name, falling back to name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?? $this->name;
    }

    /**
     * Get clean alphanumeric username.
     */
    public function getUsernameAttribute(): string
    {
        return $this->attributes['username'] ?? strtolower((string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->name));
    }

    /**
     * Get avatar url (supporting custom uploads and presets).
     */
    public function getAvatarUrlAttribute(): string
    {
        if (! empty($this->avatar_custom)) {
            $customPath = ltrim((string) $this->avatar_custom, '/');

            return str_starts_with($customPath, 'http') ? $customPath : asset($customPath);
        }

        if (empty($this->avatar) || str_contains($this->avatar, 'avatar-default')) {
            return asset('assets/characters/avatar_male_1.jpg');
        }

        $path = ltrim((string) $this->avatar, '/');
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (! str_starts_with($path, 'assets/') && ! str_starts_with($path, 'uploads/')) {
            if (str_starts_with($path, 'characters/')) {
                $path = 'assets/'.$path;
            } else {
                $path = 'assets/characters/'.$path;
            }
        }

        return asset($path);
    }

    /**
     * Get avatar url method alias for avatar_url attribute.
     */
    public function getAvatarUrl(): string
    {
        return $this->avatar_url;
    }

    /**
     * Get all unlocked sticker keys for this character.
     *
     * @return array<int, string>
     */
    public function getUnlockedStickers(): array
    {
        $base = ['skull', 'handcuffs', 'dagger', 'smoking', 'vip_star'];
        $unlocked = is_array($this->unlocked_stickers) ? $this->unlocked_stickers : [];

        if ($this->level >= 5) {
            $base[] = 'fire';
        }
        if ($this->level >= 10) {
            $base[] = 'crown';
        }
        if ($this->fights_won >= 10) {
            $base[] = 'boxing';
        }
        if ($this->thefts_succeeded >= 10) {
            $base[] = 'mask';
        }
        if ($this->cash >= 25000) {
            $base[] = 'money';
        }
        if ($this->gang_id) {
            $base[] = 'shield';
        }
        if ($this->isVip()) {
            $base[] = 'dice';
        }
        if ($this->pumpkin_seeds > 0 || $this->equipped_border) {
            $base[] = 'pumpkin';
        }
        if ($this->missions_completed >= 5) {
            $base[] = 'biohazard';
        }

        return array_values(array_unique(array_merge($base, $unlocked)));
    }

    /**
     * Get the active showcase sticker definition.
     */
    public function getActiveSticker(): ?array
    {
        if (! $this->profile_sticker) {
            return null;
        }

        return ProfileStickerCatalog::getSticker($this->profile_sticker);
    }

    /**
     * Get the active profile header background theme.
     */
    public function getActiveProfileTheme(): array
    {
        return ProfileStickerCatalog::getTheme($this->profile_bg_theme);
    }

    /**
     * Check if character is currently granted accompanied leave outside the clinic.
     */
    public function isOnLeave(): bool
    {
        return ! $this->isImprisoned() && $this->leave_until && $this->leave_until->isFuture();
    }

    /**
     * Get remaining accompanied leave time in seconds.
     */
    public function getLeaveRemainingSeconds(): int
    {
        if (! $this->isOnLeave()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->leave_until, false));
    }

    /**
     * Check if character is currently under mandatory psychiatric sedation.
     */
    public function isSedated(): bool
    {
        return $this->sedated_until && $this->sedated_until->isFuture();
    }

    /**
     * Get remaining sedation time in seconds.
     */
    public function getSedatedRemainingSeconds(): int
    {
        if (! $this->isSedated()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->sedated_until, false));
    }

    /**
     * Check how many spins the character has available today.
     */
    public function availableSpinsCount(): int
    {
        $maxSpins = $this->isVip() ? 2 : 1;
        if (! $this->last_daily_spin_at || ! $this->last_daily_spin_at->isToday()) {
            return $maxSpins;
        }

        return max(0, $maxSpins - $this->daily_spins_today);
    }

    /**
     * Can character spin the wheel of fortune right now?
     */
    public function canDailySpin(): bool
    {
        return $this->availableSpinsCount() > 0;
    }

    /**
     * Can character participate in the daily terminal hack session today?
     */
    public function canDailyHack(): bool
    {
        if (! $this->last_hack_at) {
            return true;
        }

        return ! $this->last_hack_at->isToday();
    }

    /**
     * Check if the daily login bonus window is currently active (22:00 - 22:30 Europe/Amsterdam).
     */
    public static function isLoginBonusWindowActive(?Carbon $now = null): bool
    {
        $tz = config('app.timezone', 'Europe/Amsterdam');
        $now = $now ?? Carbon::now($tz);
        $today = $now->toDateString();

        $windowStart = Carbon::parse("{$today} 22:00:00", $tz);
        $windowEnd = Carbon::parse("{$today} 22:30:00", $tz);

        return $now->betweenIncluded($windowStart, $windowEnd);
    }

    /**
     * Seconds until the next 22:00 inlog bonus window starts.
     */
    public static function secondsUntilNextLoginBonus(?Carbon $now = null): int
    {
        if (self::isLoginBonusWindowActive($now)) {
            return 0;
        }

        $tz = config('app.timezone', 'Europe/Amsterdam');
        $now = $now ?? Carbon::now($tz);
        $today = $now->toDateString();
        $startToday = Carbon::parse("{$today} 22:00:00", $tz);

        if ($now->greaterThan(Carbon::parse("{$today} 22:30:00", $tz))) {
            $nextStart = Carbon::parse("{$today} 22:00:00", $tz)->addDay();
        } else {
            $nextStart = $startToday;
        }

        return max(0, (int) $now->diffInSeconds($nextStart, false));
    }

    /**
     * Seconds remaining in the current 22:00 - 22:30 inlog bonus window.
     */
    public static function secondsRemainingInLoginBonus(?Carbon $now = null): int
    {
        if (! self::isLoginBonusWindowActive($now)) {
            return 0;
        }

        $tz = config('app.timezone', 'Europe/Amsterdam');
        $now = $now ?? Carbon::now($tz);
        $today = $now->toDateString();
        $end = Carbon::parse("{$today} 22:30:00", $tz);

        return max(0, (int) $now->diffInSeconds($end, false));
    }

    /**
     * Has character already claimed the inlog bonus streak today?
     */
    public function hasClaimedStreakToday(): bool
    {
        return (bool) ($this->last_streak_claim_at && $this->last_streak_claim_at->isToday());
    }

    /**
     * Get the next streak day number (1 to 7).
     */
    public function getNextStreakDay(): int
    {
        $currentStreak = (int) $this->streak_days;
        if ($this->last_streak_claim_at) {
            $diffDays = (int) $this->last_streak_claim_at->startOfDay()->diffInDays(now()->startOfDay());
            if ($diffDays > 1) {
                return 1;
            } elseif ($diffDays === 1) {
                return ($currentStreak % 7) + 1;
            } else {
                return max(1, $currentStreak % 7);
            }
        }

        return 1;
    }

    /**
     * Can character claim today's daily inlog bonus reward?
     * The inlog bonus is active between 22:00 and 22:30.
     */
    public function canClaimStreak(?Carbon $now = null): bool
    {
        if ($this->hasClaimedStreakToday()) {
            return false;
        }

        if (! self::isLoginBonusWindowActive($now) && ! app()->environment('testing')) {
            return false;
        }

        return true;
    }

    /**
     * Get effective number of ads watched today (resets if new day).
     */
    public function getEffectiveAdsWatchedToday(): int
    {
        if ($this->last_ad_date !== now()->toDateString()) {
            return 0;
        }

        return (int) $this->ads_watched_today;
    }

    /**
     * Get remaining ads character is allowed to watch today (max 500/day).
     */
    public function getRemainingAdsToday(): int
    {
        return max(0, 500 - $this->getEffectiveAdsWatchedToday());
    }

    /**
     * Get cooldown between ads in seconds (0s = geen wachttijd, direct doorkijken).
     */
    public function getRemainingAdCooldownSeconds(): int
    {
        return 0;
    }

    /**
     * Can character watch a sponsor ad right now?
     */
    public function canWatchAd(): bool
    {
        return $this->getRemainingAdsToday() > 0;
    }

    /**
     * Progress percentage toward next 250-ad 24h VIP milestone.
     */
    public function getVipAdMilestoneProgress(): float
    {
        $currentInTier = $this->ads_watched_total % 250;

        return min(100.0, round(($currentInTier / 250) * 100, 1));
    }

    /**
     * How many ads left until the next 250-ad 24h VIP unlock.
     */
    public function getAdsUntilNextVip(): int
    {
        $currentInTier = $this->ads_watched_total % 250;

        return 250 - $currentInTier;
    }

    /**
     * Get active bounties on this character.
     */
    public function activeBounties()
    {
        return $this->hasMany(Bounty::class, 'target_character_id')->where('is_claimed', false);
    }

    /**
     * Total bounty on this character's head.
     */
    public function totalBountyAmount(): int
    {
        return (int) $this->activeBounties()->sum('amount');
    }

    protected static function booted(): void
    {
        static::saving(function (Character $character) {
            if ($character->isDirty('respect') && ! $character->isDirty('today_respect')) {
                $oldRespect = (float) ($character->getOriginal('respect') ?? 0);
                $newRespect = (float) ($character->respect ?? 0);
                $diff = $newRespect - $oldRespect;
                if ($diff > 0) {
                    $today = now()->format('Y-m-d');
                    $currentDate = $character->today_respect_date ? Carbon::parse($character->today_respect_date)->format('Y-m-d') : null;
                    if ($currentDate !== $today) {
                        $character->today_respect = 0.00;
                        $character->today_respect_date = $today;
                    }
                    $character->today_respect = round(((float) ($character->today_respect ?? 0)) + $diff, 2);
                }
            }
        });
    }

    /**
     * Check if character has active VIP status (either personal VIP or through Gang Overstap VIP boost).
     */
    public function isVip(): bool
    {
        $tier = (int) ($this->perks['supporter_tier'] ?? 0);

        if ($this->is_vip && $tier === 4) {
            return true;
        }

        if ($this->is_vip && (! $this->vip_expires_at || $this->vip_expires_at->isFuture())) {
            return true;
        }

        // Heal characters whose paid/admin-granted supporter tier was saved while a stale pre-tier trial timestamp remained in vip_expires_at.
        if ($this->is_vip && $tier >= 1 && $this->vip_expires_at && $this->vip_expires_at->lt(Carbon::create(2026, 11, 1, 0, 0, 0))) {
            return true;
        }

        if ($this->gang && $this->gang->hasActiveVipBoost()) {
            return true;
        }

        return false;
    }

    /**
     * Get VIP Supporter Tier (0 = Free/None, 1 = VIP Lid, 2 = Gangster Elite, 3 = Kliniek Baas, 4 = Lifetime).
     */
    public function getSupporterTier(): int
    {
        if (! $this->isVip()) {
            return 0;
        }

        return (int) ($this->perks['supporter_tier'] ?? 1);
    }

    /**
     * Get the number of unclaimed Battle Pass rewards currently unlocked for this character.
     */
    public function getUnclaimedBattlePassCount(): int
    {
        $maxUnlocked = min(150, max(0, (int) $this->level));
        if ($maxUnlocked < 1) {
            return 0;
        }

        $claims = $this->perks['battle_pass_claims'] ?? [];
        if (! is_array($claims)) {
            $claims = [];
        }
        $claimLookup = array_flip($claims);

        $isVip = $this->isVip();
        $tier = $this->getSupporterTier();

        $count = 0;
        for ($lvl = 1; $lvl <= $maxUnlocked; $lvl++) {
            if (! isset($claimLookup["lvl_{$lvl}_free"])) {
                $count++;
            }
            if ($isVip && $tier >= 1 && ! isset($claimLookup["lvl_{$lvl}_vip1"])) {
                $count++;
            }
            if ($isVip && $tier >= 2 && ! isset($claimLookup["lvl_{$lvl}_vip2"])) {
                $count++;
            }
            if ($isVip && $tier >= 3 && ! isset($claimLookup["lvl_{$lvl}_vip3"])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get rich VIP badge details for profile, lists, and comments.
     *
     * @return array{tier: int, label: string, short_label: string, icon: string, class: string, border_color: string, glow: string}|null
     */
    public function getVipBadgeAttribute(): ?array
    {
        if (! $this->isVip()) {
            return null;
        }

        $tier = $this->getSupporterTier();

        return match ($tier) {
            4 => [
                'tier' => 4,
                'label' => 'Lifetime V.I.P.',
                'short_label' => 'Lifetime',
                'icon' => 'fa-crown',
                'class' => 'bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 text-slate-950 border-amber-300 shadow-md shadow-amber-500/20',
                'border_color' => 'border-amber-400',
                'glow' => 'shadow-[0_0_15px_rgba(251,191,36,0.6)]',
            ],
            3 => [
                'tier' => 3,
                'label' => 'Kliniek Baas V.I.P.',
                'short_label' => 'Tier 3 Baas',
                'icon' => 'fa-dragon',
                'class' => 'bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-800 text-white border-purple-400 shadow-md shadow-purple-600/30',
                'border_color' => 'border-purple-500',
                'glow' => 'shadow-[0_0_15px_rgba(168,85,247,0.6)]',
            ],
            2 => [
                'tier' => 2,
                'label' => 'Gangster Elite V.I.P.',
                'short_label' => 'Tier 2 Elite',
                'icon' => 'fa-gem',
                'class' => 'bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white border-emerald-400 shadow-md shadow-emerald-500/20',
                'border_color' => 'border-emerald-500',
                'glow' => 'shadow-[0_0_12px_rgba(16,185,129,0.5)]',
            ],
            default => [
                'tier' => 1,
                'label' => 'TBS V.I.P. Lid',
                'short_label' => 'Tier 1 VIP',
                'icon' => 'fa-star',
                'class' => 'bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white border-blue-400 shadow-md shadow-blue-500/20',
                'border_color' => 'border-blue-400',
                'glow' => 'shadow-[0_0_10px_rgba(59,130,246,0.4)]',
            ],
        };
    }

    /**
     * Catalog of solid name colors available for VIP Tier 2+ / Staff.
     *
     * @return array<string, array{hex: string, name: string, class: string, glow: string}>
     */
    public static function nameColorCatalog(): array
    {
        return [
            '#f59e0b' => ['hex' => '#f59e0b', 'name' => 'Goudgeel', 'class' => 'bg-amber-500', 'glow' => 'rgba(245, 158, 11, 0.55)'],
            '#a855f7' => ['hex' => '#a855f7', 'name' => 'TBS Neon Paars', 'class' => 'bg-purple-500', 'glow' => 'rgba(168, 85, 247, 0.55)'],
            '#10b981' => ['hex' => '#10b981', 'name' => 'Smaragd Groen', 'class' => 'bg-emerald-500', 'glow' => 'rgba(16, 185, 129, 0.55)'],
            '#ef4444' => ['hex' => '#ef4444', 'name' => 'Bloedrood', 'class' => 'bg-rose-500', 'glow' => 'rgba(239, 68, 68, 0.55)'],
            '#06b6d4' => ['hex' => '#06b6d4', 'name' => 'Cyber Cyaan', 'class' => 'bg-cyan-500', 'glow' => 'rgba(6, 182, 212, 0.55)'],
            '#ec4899' => ['hex' => '#ec4899', 'name' => 'Neon Roze', 'class' => 'bg-pink-500', 'glow' => 'rgba(236, 72, 153, 0.55)'],
            '#3b82f6' => ['hex' => '#3b82f6', 'name' => 'Elektrisch Blauw', 'class' => 'bg-blue-500', 'glow' => 'rgba(59, 130, 246, 0.55)'],
            '#e2e8f0' => ['hex' => '#e2e8f0', 'name' => 'Platina Wit', 'class' => 'bg-slate-200', 'glow' => 'rgba(226, 232, 240, 0.55)'],
            '#84cc16' => ['hex' => '#84cc16', 'name' => 'Gifgroen', 'class' => 'bg-lime-500', 'glow' => 'rgba(132, 204, 22, 0.55)'],
            '#f97316' => ['hex' => '#f97316', 'name' => 'Vuur Oranje', 'class' => 'bg-orange-500', 'glow' => 'rgba(249, 115, 22, 0.55)'],
            '#d946ef' => ['hex' => '#d946ef', 'name' => 'Fuchsia Magie', 'class' => 'bg-fuchsia-500', 'glow' => 'rgba(217, 70, 239, 0.55)'],
            '#14b8a6' => ['hex' => '#14b8a6', 'name' => 'Turkoois', 'class' => 'bg-teal-500', 'glow' => 'rgba(20, 184, 166, 0.55)'],
        ];
    }

    /**
     * Catalog of multi-stop name gradients (Kleuroverlopen).
     *
     * @return array<string, array{id: string, name: string, css: string, glow: string, badge: string}>
     */
    public static function nameGradientCatalog(): array
    {
        return [
            'none' => [
                'id' => 'none',
                'name' => 'Geen Overloop (Effen Kleur)',
                'css' => '',
                'glow' => 'rgba(168, 85, 247, 0.45)',
                'badge' => 'Effen',
            ],
            'syndicate' => [
                'id' => 'syndicate',
                'name' => 'TBS Syndicaat (Paars • Roze • Goud)',
                'css' => 'linear-gradient(90deg, #c084fc, #f472b6, #fbbf24, #f472b6, #c084fc)',
                'glow' => 'rgba(192, 132, 252, 0.6)',
                'badge' => 'Populair',
            ],
            'gold_king' => [
                'id' => 'gold_king',
                'name' => 'Gouden Koning (Goud • Diamant • Amber)',
                'css' => 'linear-gradient(90deg, #f59e0b, #fef08a, #d97706, #fef08a, #f59e0b)',
                'glow' => 'rgba(245, 158, 11, 0.6)',
                'badge' => 'Luxe',
            ],
            'cyber_neon' => [
                'id' => 'cyber_neon',
                'name' => 'Cyber Neon (Cyaan • Blauw • Paars)',
                'css' => 'linear-gradient(90deg, #22d3ee, #3b82f6, #a855f7, #3b82f6, #22d3ee)',
                'glow' => 'rgba(34, 211, 238, 0.6)',
                'badge' => 'Neon',
            ],
            'blood_fire' => [
                'id' => 'blood_fire',
                'name' => 'Bloed & Vuur (Rood • Oranje • Goud)',
                'css' => 'linear-gradient(90deg, #ef4444, #f97316, #facc15, #f97316, #ef4444)',
                'glow' => 'rgba(239, 68, 68, 0.6)',
                'badge' => 'Agressief',
            ],
            'emerald_matrix' => [
                'id' => 'emerald_matrix',
                'name' => 'Smaragd Gif (Smaragd • Lime • Cyaan)',
                'css' => 'linear-gradient(90deg, #10b981, #84cc16, #06b6d4, #84cc16, #10b981)',
                'glow' => 'rgba(16, 185, 129, 0.6)',
                'badge' => 'Matrix',
            ],
            'royal_amethyst' => [
                'id' => 'royal_amethyst',
                'name' => 'Royal Amethist (Indigo • Paars • Fuchsia)',
                'css' => 'linear-gradient(90deg, #818cf8, #c084fc, #e879f9, #c084fc, #818cf8)',
                'glow' => 'rgba(168, 85, 247, 0.6)',
                'badge' => 'Kliniek',
            ],
            'ice_frost' => [
                'id' => 'ice_frost',
                'name' => 'IJs & Kristal (Cyaan • IJswit • Blauw)',
                'css' => 'linear-gradient(90deg, #38bdf8, #e0f2fe, #0284c7, #e0f2fe, #38bdf8)',
                'glow' => 'rgba(56, 189, 248, 0.6)',
                'badge' => 'Koel',
            ],
            'rainbow_prism' => [
                'id' => 'rainbow_prism',
                'name' => 'Kliniek Prisma (Volledig Regenboog)',
                'css' => 'linear-gradient(90deg, #ff66b2, #a855f7, #38bdf8, #10b981, #f59e0b, #ff66b2)',
                'glow' => 'rgba(236, 72, 153, 0.6)',
                'badge' => 'Exclusief',
            ],
        ];
    }

    /**
     * Catalog of name glow intensities/styles.
     *
     * @return array<string, array{id: string, name: string, desc: string}>
     */
    public static function nameGlowCatalog(): array
    {
        return [
            'none' => ['id' => 'none', 'name' => 'Geen Gloed (Strak)', 'desc' => 'Scherpe letters zonder lichthalo'],
            'subtle' => ['id' => 'subtle', 'name' => 'Subtiele Gloed', 'desc' => 'Zachte elegante gloed om de letters'],
            'neon' => ['id' => 'neon', 'name' => 'Sterke Neon Glow', 'desc' => 'Heldere opvallende neon uitstraling'],
            'pulse' => ['id' => 'pulse', 'name' => 'Ademende Pulse Glow', 'desc' => 'Dynamisch pulserend lichteffect'],
        ];
    }

    /**
     * Can the character customize their name color (Tier 2+ or explicit perk or Staff).
     */
    public function canCustomizeNameColor(): bool
    {
        return ($this->isVip() && ($this->getSupporterTier() >= 2 || ! empty($this->perks['has_custom_name_color'])))
            || (bool) ($this->user?->isStaff());
    }

    /**
     * Can the character toggle glitter sparkle effect (Tier 3+ or explicit perk or Staff).
     */
    public function canUseSparkleEffect(): bool
    {
        return ($this->isVip() && ($this->getSupporterTier() >= 3 || ! empty($this->perks['has_sparkle_effect'])))
            || (bool) ($this->user?->isStaff());
    }

    /**
     * Get active custom name color if eligible.
     */
    public function getCustomNameColor(): ?string
    {
        if (! $this->canCustomizeNameColor()) {
            return null;
        }

        return $this->perks['custom_name_color'] ?? null;
    }

    /**
     * Is the glitter sparkle effect currently active.
     */
    public function isSparkleActive(): bool
    {
        if (! $this->canUseSparkleEffect()) {
            return false;
        }

        return (bool) ($this->perks['sparkle_enabled'] ?? false);
    }

    /**
     * Get active custom name gradient key ('none' or a catalog key).
     */
    public function getCustomNameGradient(): string
    {
        if (! $this->canCustomizeNameColor() && ! $this->canUseSparkleEffect()) {
            return 'none';
        }

        $gradient = $this->perks['custom_name_gradient'] ?? null;
        $catalog = self::nameGradientCatalog();

        if (is_string($gradient) && isset($catalog[$gradient])) {
            return $gradient;
        }

        if ($this->isSparkleActive()) {
            return 'syndicate';
        }

        return 'none';
    }

    /**
     * Get active custom name glow key ('none', 'subtle', 'neon', 'pulse').
     */
    public function getCustomNameGlow(): string
    {
        if (! $this->canCustomizeNameColor() && ! $this->canUseSparkleEffect()) {
            return 'none';
        }

        $glow = $this->perks['custom_name_glow'] ?? null;
        $catalog = self::nameGlowCatalog();

        if (is_string($glow) && isset($catalog[$glow])) {
            return $glow;
        }

        if ($this->isSparkleActive()) {
            return 'subtle';
        }

        return 'none';
    }

    /**
     * Compute the CSS classes to apply to this character's rendered name.
     */
    public function getNameCssClasses(string $defaultColorClass = 'text-slate-900 dark:text-white'): string
    {
        $gradient = $this->getCustomNameGradient();
        $sparkle = $this->isSparkleActive();
        $glow = $this->getCustomNameGlow();
        $color = $this->getCustomNameColor();

        $classes = [];

        if ($gradient !== 'none' || $sparkle) {
            $classes[] = 'tbs-name-gradient';
            $classes[] = 'tbs-name-shimmer';
            if ($sparkle) {
                $classes[] = 'vip-sparkle-name';
            }
        } elseif (! $color && $defaultColorClass !== '') {
            $classes[] = $defaultColorClass;
        }

        if ($glow === 'subtle') {
            $classes[] = 'tbs-name-glow-subtle';
        } elseif ($glow === 'neon') {
            $classes[] = 'tbs-name-glow-neon';
        } elseif ($glow === 'pulse') {
            $classes[] = 'tbs-name-glow-pulse';
        }

        return implode(' ', $classes);
    }

    /**
     * Compute the inline CSS style string to apply to this character's rendered name.
     */
    public function getNameInlineStyle(): string
    {
        $gradient = $this->getCustomNameGradient();
        $sparkle = $this->isSparkleActive();
        $glow = $this->getCustomNameGlow();
        $color = $this->getCustomNameColor();

        $styles = [];
        $gradientCatalog = self::nameGradientCatalog();
        $colorCatalog = self::nameColorCatalog();

        if ($gradient !== 'none' && isset($gradientCatalog[$gradient])) {
            $gradInfo = $gradientCatalog[$gradient];
            $styles[] = '--tbs-name-gradient: '.$gradInfo['css'];
            $styles[] = '--tbs-glow-color: '.$gradInfo['glow'];
        } elseif ($sparkle) {
            $baseHex = $color ?: '#c084fc';
            $glowRgba = $colorCatalog[$baseHex]['glow'] ?? 'rgba(192, 132, 252, 0.6)';
            $styles[] = "--tbs-name-gradient: linear-gradient(90deg, {$baseHex}, #fef08a, #f472b6, {$baseHex})";
            $styles[] = "--tbs-glow-color: {$glowRgba}";
        } elseif ($color) {
            $glowRgba = $colorCatalog[$color]['glow'] ?? 'rgba(168, 85, 247, 0.55)';
            $styles[] = "color: {$color} !important";
            if ($glow !== 'none') {
                $styles[] = "--tbs-glow-color: {$glowRgba}";
            }
        } elseif ($glow !== 'none') {
            $styles[] = '--tbs-glow-color: rgba(168, 85, 247, 0.55)';
        }

        return implode('; ', $styles);
    }

    /**
     * Check if character has claimed the Discord link bonus.
     */
    public function hasClaimedDiscordReward(): bool
    {
        return ! is_null($this->discord_reward_claimed_at);
    }

    /**
     * Grant the Discord connection starter bonus (€2,500 cash + 3 days VIP).
     */
    public function claimDiscordReward(): bool
    {
        if ($this->hasClaimedDiscordReward()) {
            return false;
        }

        $this->cash += 2500;
        $this->is_vip = true;
        $currentExpiry = ($this->vip_expires_at && $this->vip_expires_at->isFuture())
            ? $this->vip_expires_at
            : now();
        $this->vip_expires_at = $currentExpiry->addDays(3);
        $this->discord_reward_claimed_at = now();
        $this->save();

        return true;
    }

    /**
     * Get number of attacks performed today (resets automatically at midnight).
     */
    public function getTodayAttacksCountAttribute(): int
    {
        if ($this->daily_attacks_date !== now()->format('Y-m-d')) {
            return 0;
        }

        return $this->daily_attacks_count ?? 0;
    }

    /**
     * Get remaining daily attacks (100 for normal, 1000 for VIP).
     */
    public function getRemainingDailyAttacksAttribute(): int
    {
        $max = $this->isVip() ? 1000 : 100;

        return max(0, $max - $this->today_attacks_count);
    }

    /**
     * Get received messages.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(GameMessage::class, 'recipient_id')->latest();
    }

    /**
     * Get sent messages.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(GameMessage::class, 'sender_id')->latest();
    }

    /**
     * Get the user that owns the character.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memoryStat(): HasOne
    {
        return $this->hasOne(CharacterMemoryStat::class);
    }

    /**
     * Get the gang the character belongs to.
     */
    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    /**
     * Get loans taken by this character.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get active loans taken by this character.
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'active');
    }

    /**
     * Get bank and financial transactions of this character.
     */
    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Referrals invited by this character.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Referral record for when this character was invited.
     */
    public function referredByRecord(): HasOne
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    /**
     * Recent visits received on this character's profile.
     */
    public function profileVisitsReceived(): HasMany
    {
        return $this->hasMany(ProfileVisit::class, 'visited_id');
    }

    /**
     * Profiles visited by this character.
     */
    public function profileVisitsMade(): HasMany
    {
        return $this->hasMany(ProfileVisit::class, 'visitor_id');
    }

    /**
     * Get completed missions records.
     */
    public function missions(): HasMany
    {
        return $this->hasMany(CharacterMission::class);
    }

    /**
     * Check if character has already completed a given mission.
     */
    public function hasCompletedMission(Mission|int $mission): bool
    {
        $missionId = $mission instanceof Mission ? $mission->id : $mission;

        return $this->missions()
            ->where('mission_id', $missionId)
            ->where('completed_count', '>', 0)
            ->exists();
    }

    /**
     * Get guestbook entries for this character.
     */
    public function guestbookEntries(): HasMany
    {
        return $this->hasMany(GuestbookEntry::class, 'profile_character_id')->latest();
    }

    public function friends(): HasMany
    {
        return $this->hasMany(Friend::class);
    }

    public function timelinePosts(): HasMany
    {
        return $this->hasMany(TimelinePost::class)->latest();
    }

    /**
     * Get notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(GameNotification::class)->latest();
    }

    /**
     * Get character items in inventory.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CharacterItem::class)->where('quantity', '>', 0);
    }

    /**
     * Get consumable food, drink and medicine items in inventory.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, CharacterItem>
     */
    public function getConsumableInventoryItems(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->items()
            ->where('quantity', '>', 0)
            ->whereHas('item', function ($q) {
                $q->where('category', 'consumable')
                    ->orWhere('energy_restore', '>', 0)
                    ->orWhere('health_restore', '>', 0);
            })
            ->with('item')
            ->get()
            ->filter(fn ($ci) => ! in_array($ci->item?->slug, ['bivakmuts-masker', 'smokkelpakketje', 'ijzerzaagje'], true))
            ->values();
    }

    /**
     * Get total item bonus for a specific stat.
     * Capped at 1 of each unique equipment item in backpack to prevent stacking duplicate items infinitely.
     */
    public function getItemBonus(string $stat): int
    {
        $bonusColumn = $stat.'_bonus';

        return (int) $this->items()
            ->whereHas('item', fn ($q) => $q->where($bonusColumn, '>', 0))
            ->with('item')
            ->get()
            ->unique('item_id')
            ->sum(fn ($ci) => $ci->item->{$bonusColumn} ?? 0);
    }

    public function getEffectiveStrength(): int
    {
        return (int) ($this->strength ?? 10) + $this->getItemBonus('strength');
    }

    public function getEffectiveDefense(): int
    {
        return (int) ($this->defense ?? 10) + $this->getItemBonus('defense');
    }

    public function getEffectiveStamina(): int
    {
        return (int) ($this->stamina ?? 10) + $this->getItemBonus('stamina');
    }

    public function getEffectiveIntelligence(): int
    {
        return (int) ($this->intelligence ?? 10) + $this->getItemBonus('intelligence');
    }

    public function getEffectiveCharisma(): int
    {
        return (int) ($this->charisma ?? 10) + $this->getItemBonus('charisma');
    }

    public function getEffectiveDiscipline(): int
    {
        return (int) ($this->discipline ?? 10) + $this->getItemBonus('discipline');
    }

    /**
     * Maximum natural stat cap based on character level.
     * Generous dynamic cap (100 base + 50 per level) that prevents low-level twink exploits
     * while giving active players plenty of training room per level.
     */
    public function getMaxStatForLevel(): int
    {
        return 100 + ((int) ($this->level ?? 1) * 50);
    }

    /**
     * Return the XP required to reach the next level for this character.
     */
    public function xpForNextLevel(): int
    {
        return app(LevelService::class)->getXpRequiredForNextLevel((int) ($this->level ?? 1));
    }

    /**
     * Synchronize all six core attributes so none exceed the current level ceiling.
     * Converts any historical stat overflow into XP first so players retain their effort as level progression.
     */
    public function syncStatsWithLevelCap(): bool
    {
        $stats = ['strength', 'defense', 'stamina', 'intelligence', 'charisma', 'discipline'];
        $maxStat = $this->getMaxStatForLevel();
        $overflowPoints = 0;

        foreach ($stats as $stat) {
            $val = (int) ($this->{$stat} ?? 10);
            if ($val > $maxStat) {
                $overflowPoints += ($val - $maxStat);
            }
        }

        if ($overflowPoints <= 0) {
            return false;
        }

        app(LevelService::class)->addXp($this, $overflowPoints * 45);
        $newMaxStat = $this->getMaxStatForLevel();

        foreach ($stats as $stat) {
            if ((int) ($this->{$stat} ?? 10) > $newMaxStat) {
                $this->{$stat} = $newMaxStat;
            }
        }

        $this->save();

        return true;
    }

    /**
     * Check if character has set up a 4-digit Geldmaat ATM PIN.
     */
    public function hasBankPin(): bool
    {
        $perks = $this->perks ?? [];

        return ! empty($perks['bank_pin']);
    }

    /**
     * Set or update character's 4-digit Geldmaat ATM PIN.
     */
    public function setBankPin(string $pin): void
    {
        $perks = $this->perks ?? [];
        $perks['bank_pin'] = Hash::make($pin);
        $this->perks = $perks;
        $this->save();
    }

    /**
     * Verify if provided PIN matches stored ATM PIN.
     */
    public function verifyBankPin(string $pin): bool
    {
        if (! $this->hasBankPin()) {
            return false;
        }

        return Hash::check($pin, (string) ($this->perks['bank_pin'] ?? ''));
    }

    /**
     * Get vault codes owned by this character.
     */
    public function vaultCodes(): HasMany
    {
        return $this->hasMany(VaultCode::class, 'character_id');
    }

    /**
     * Get unredeemed vault codes owned by this character.
     */
    public function unredeemedVaultCodes(): HasMany
    {
        return $this->hasMany(VaultCode::class, 'character_id')->where('is_redeemed', false);
    }

    public function unreadDirectMessagesCount(): int
    {
        return $this->unreadDirectMessagesCountValue ??= DirectMessage::where('recipient_id', $this->id)->where('is_read', false)->count();
    }

    /**
     * Check if currently serving prison time.
     */
    public function isImprisoned(): bool
    {
        $until = $this->prison_until;
        $hasTime = $until && now()->lt($until);

        if (! $this->is_in_prison && ! $hasTime) {
            return false;
        }

        if ($hasTime) {
            return true;
        }

        $this->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_reason' => null,
            'ad_prison_progress' => 0,
            'prison_escape_attempted' => false,
        ]);

        return false;
    }

    /**
     * Calculate how many sponsor ads are required to get an instant pardon based on sentence duration.
     */
    public function getRequiredPrisonAds(): int
    {
        if (! $this->isImprisoned()) {
            return 1;
        }

        $sec = max(0, (int) now()->diffInSeconds($this->prison_until, false));

        if ($sec <= 90) {
            return 1;
        }
        if ($sec <= 240) {
            return 2;
        }
        if ($sec <= 480) {
            return 3;
        }
        if ($sec <= 720) {
            return 4;
        }

        return 5;
    }

    /**
     * Calculate remaining ads needed to get released from prison.
     */
    public function getRemainingPrisonAds(): int
    {
        $required = $this->getRequiredPrisonAds();
        $progress = (int) ($this->ad_prison_progress ?? 0);

        return max(1, $required - $progress);
    }

    /**
     * Get remaining prison time in seconds.
     */
    public function remainingPrisonSeconds(): int
    {
        $until = $this->prison_until;
        if (! $until) {
            return 0;
        }

        $secs = (int) now()->diffInSeconds($until, false);
        if ($secs <= 0) {
            if ($this->is_in_prison) {
                $this->update([
                    'is_in_prison' => false,
                    'prison_until' => null,
                    'prison_reason' => null,
                ]);
            }

            return 0;
        }

        return $secs;
    }

    /**
     * Get remaining prison time in minutes.
     */
    public function remainingPrisonMinutes(): int
    {
        $secs = $this->remainingPrisonSeconds();

        return (int) ceil($secs / 60);
    }

    /**
     * Check if currently under 24h VIP cash protection.
     */
    public function hasCashProtection(): bool
    {
        return (bool) ($this->cash_protection_until && now()->lt($this->cash_protection_until));
    }

    /**
     * Check whether the character has active isolation or shield protection.
     */
    public function hasIsolationProtection(): bool
    {
        return (bool) ($this->protection_until && now()->lt($this->protection_until));
    }

    /**
     * Get the remaining isolation or shield protection time in seconds.
     */
    public function getIsolationProtectionRemainingSeconds(): int
    {
        if (! $this->hasIsolationProtection()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->protection_until, false));
    }

    /**
     * Get the remaining cash protection time in seconds.
     */
    public function getCashProtectionRemainingSeconds(): int
    {
        if (! $this->hasCashProtection()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->cash_protection_until, false));
    }

    /**
     * Get the maximum remaining protection time in seconds across isolation and cash protection.
     */
    public function getProtectionRemainingSeconds(): int
    {
        return max(
            $this->getIsolationProtectionRemainingSeconds(),
            $this->getCashProtectionRemainingSeconds()
        );
    }

    /**
     * Get the latest active protection expiration timestamp.
     */
    public function getProtectionExpiresAt(): ?Carbon
    {
        $iso = $this->hasIsolationProtection() ? $this->protection_until : null;
        $cash = $this->hasCashProtection() ? $this->cash_protection_until : null;

        if ($iso && $cash) {
            return $iso->gt($cash) ? $iso : $cash;
        }

        return $iso ?? $cash;
    }

    /**
     * Check if currently under voluntary isolation protection or cash protection.
     */
    public function isProtected(): bool
    {
        return $this->hasIsolationProtection() || $this->hasCashProtection();
    }

    /**
     * Check how many times the character has suffered a fatal liquidation today.
     */
    public function getDailyDeathsCount(): int
    {
        if ($this->daily_deaths_date !== now()->format('Y-m-d')) {
            return 0;
        }

        return $this->daily_deaths_count ?? 0;
    }

    /**
     * Record a fatal liquidation against this character today.
     */
    public function incrementDailyDeaths(): void
    {
        $today = now()->format('Y-m-d');
        if ($this->daily_deaths_date !== $today) {
            $this->daily_deaths_count = 1;
            $this->daily_deaths_date = $today;
        } else {
            $this->daily_deaths_count = ($this->daily_deaths_count ?? 0) + 1;
        }

        $this->save();
    }

    /**
     * Cancel voluntary or recovery protection when the character initiates an attack.
     * Note: Paid 24h VIP Cash Protection is preserved and NEVER breaks when attacking!
     */
    public function breakProtection(): void
    {
        if ($this->protection_until && now()->lt($this->protection_until)) {
            $this->protection_until = null;
            $this->save();
        }
    }

    /**
     * In-memory cache for character cooldowns to eliminate N+1 queries.
     *
     * @var Collection<string, CharacterCooldown>|null
     */
    public ?Collection $cooldownsMap = null;

    /**
     * Cooldown records for this character.
     */
    public function cooldowns(): HasMany
    {
        return $this->hasMany(CharacterCooldown::class);
    }

    /**
     * Get remaining cooldown seconds for an action (or 0 if not on cooldown).
     */
    public function cooldownRemaining(string $action): int
    {
        if ($this->relationLoaded('cooldowns')) {
            $cd = $this->cooldowns->firstWhere('action', $action);
        } else {
            if ($this->cooldownsMap === null) {
                $this->cooldownsMap = $this->cooldowns()->get()->keyBy('action');
            }
            $cd = $this->cooldownsMap->get($action);
        }

        if (! $cd || ! $cd->cooldown_until) {
            return 0;
        }

        $diff = now()->diffInSeconds($cd->cooldown_until, false);

        return max(0, (int) $diff);
    }

    /**
     * Get cached top rankings by respect.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Character>|Collection<int, Character>
     */
    public static function getTopRankings(int $limit = 10)
    {
        $cached = Cache::get('tbs_top_rankings_'.$limit);
        if ($cached instanceof \Illuminate\Database\Eloquent\Collection || $cached instanceof Collection) {
            return $cached;
        }

        $rankings = static::orderByDesc('respect')->take($limit)->get();
        Cache::put('tbs_top_rankings_'.$limit, $rankings, 60);

        return $rankings;
    }

    /**
     * Check if character is currently on cooldown for an action.
     */
    public function isOnCooldown(string $action): bool
    {
        return $this->cooldownRemaining($action) > 0;
    }

    /**
     * Get support tickets created by character.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get vehicles owned by this character.
     */
    public function characterVehicles(): HasMany
    {
        return $this->hasMany(CharacterVehicle::class);
    }

    /**
     * Get currently active getaway vehicle.
     */
    public function activeVehicle(): ?CharacterVehicle
    {
        return $this->characterVehicles()->where('in_gang_garage', false)->where('is_active', true)->with('vehicle')->first()
            ?? $this->characterVehicles()->where('in_gang_garage', false)->with('vehicle')->first();
    }

    /**
     * Get heist history for this character.
     */
    public function heistLogs(): HasMany
    {
        return $this->hasMany(HeistLog::class);
    }

    /**
     * Get hacking logs for this character.
     */
    public function hackingLogs(): HasMany
    {
        return $this->hasMany(HackingLog::class);
    }

    /**
     * Check if this character is accepted friends with another character.
     */
    public function isFriendWith(Character|int $other): bool
    {
        $otherId = $other instanceof Character ? $other->id : $other;

        return Friend::where('character_id', $this->id)
            ->where('friend_id', $otherId)
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Check if this character has gang officer/management permissions in their gang.
     */
    public function isGangOfficer(): bool
    {
        if (! $this->gang_id) {
            return false;
        }

        return $this->gang?->isOfficer($this) ?? false;
    }

    /**
     * Check if character user can moderate content across the platform.
     */
    public function canModerate(): bool
    {
        return $this->user?->canModerate() ?? false;
    }

    /**
     * Check if character user is owner.
     */
    public function isOwner(): bool
    {
        return $this->user?->isOwner() ?? false;
    }

    /**
     * Check if character user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->user?->isAdmin() ?? false;
    }

    /**
     * Check if character user is moderator.
     */
    public function isModerator(): bool
    {
        return $this->user?->isModerator() ?? false;
    }

    /**
     * Add respect to character and track daily respect earned.
     */
    public function addRespect(float|int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $today = now()->format('Y-m-d');
        $currentDate = $this->today_respect_date ? Carbon::parse($this->today_respect_date)->format('Y-m-d') : null;

        if ($currentDate !== $today) {
            $this->today_respect = 0.00;
            $this->today_respect_date = $today;
        }

        $this->respect = max(0, (int) round(($this->respect ?? 0) + $amount));
        $this->today_respect = round(((float) ($this->today_respect ?? 0)) + $amount, 2);

        try {
            if (class_exists(DailyBattleService::class)) {
                app(DailyBattleService::class)->record($this, 'respect', (int) round($amount));
            }
        } catch (\Throwable) {
            // Ignore in environments where service/battle is unavailable
        }
    }

    /**
     * Get today's respect score (reset if new day).
     */
    public function getTodayRespectScoreAttribute(): float
    {
        $today = now()->format('Y-m-d');
        $currentDate = $this->today_respect_date ? Carbon::parse($this->today_respect_date)->format('Y-m-d') : null;

        if ($currentDate !== $today) {
            return 0.00;
        }

        return (float) ($this->today_respect ?? 0.00);
    }

    /**
     * Formatted respect (e.g. 6.73K or 1.25M or 450).
     */
    public function getFormattedRespectAttribute(): string
    {
        $val = (float) ($this->respect ?? 0);
        if ($val >= 1000000) {
            return number_format($val / 1000000, 2, '.', '').'M';
        }
        if ($val >= 1000) {
            return number_format($val / 1000, 2, '.', '').'K';
        }

        return (string) number_format($val, 0, ',', '.');
    }

    /**
     * Formatted today's respect (e.g. 68.30).
     */
    public function getFormattedTodayRespectAttribute(): string
    {
        return number_format($this->today_respect_score, 2, '.', '');
    }

    /**
     * Check if character is considered online (active in the past 5 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_active_at && $this->last_active_at->isAfter(now()->subMinutes(5));
    }

    /**
     * Get human readable online status label.
     */
    public function getOnlineStatusLabelAttribute(): string
    {
        if ($this->isOnline()) {
            return 'Nu Online';
        }

        return $this->last_active_at ? 'Laatst gezien '.$this->last_active_at->diffForHumans() : 'Nog niet actief';
    }

    /**
     * Get count of currently online characters (minimum 1 if user is active).
     */
    public static function getOnlineCount(): int
    {
        return Cache::remember('tbs_online_count', 45, function () {
            $count = static::where('last_active_at', '>=', now()->subMinutes(5))->count();

            return max(1, $count);
        });
    }

    /**
     * Profile respects received from other players.
     */
    public function receivedRespects(): HasMany
    {
        return $this->hasMany(ProfileRespect::class, 'receiver_id');
    }

    /**
     * Profile respects given to other players.
     */
    public function givenRespects(): HasMany
    {
        return $this->hasMany(ProfileRespect::class, 'giver_id');
    }

    /**
     * Get all available character titles with unlock status.
     *
     * @return list<array{id: string, name: string, unlocked: bool, description: string, icon: string}>
     */
    public function getAvailableTitles(): array
    {
        $totalWealth = $this->cash + $this->bank;

        $titles = [
            [
                'id' => 'Modelpatiënt',
                'name' => 'Modelpatiënt (Schijn bedriegt)',
                'unlocked' => true,
                'description' => 'Altijd beleefd tegen de bewakers, maar in stilte plannend.',
                'requirement' => 'Altijd beleefd tegen de bewakers, maar in stilte plannend.',
                'icon' => 'fa-solid fa-face-smile',
            ],
            [
                'id' => 'Sigarettensmokkelaar',
                'name' => 'Sigarettensmokkelaar',
                'unlocked' => $this->level >= 2,
                'description' => 'Voorziet de afdeling van broodnodige rookwaren (Level 2+).',
                'requirement' => 'Level 2+',
                'icon' => 'fa-solid fa-smoking',
            ],
            [
                'id' => 'Luchtplaats Vechter',
                'name' => 'Luchtplaats Vechter',
                'unlocked' => $this->fights_won >= 3,
                'description' => 'Gevreesd tijdens het dagelijkse luchtuurtje (3+ gevechten gewonnen).',
                'requirement' => '3+ Gevechten gewonnen',
                'icon' => 'fa-solid fa-hand-fist',
            ],
            [
                'id' => 'Gevaarlijk Onvoorspelbaar',
                'name' => 'Gevaarlijk Onvoorspelbaar',
                'unlocked' => $this->level >= 5,
                'description' => 'Zelfs de bewaarders lopen met een boog om je heen (Level 5+).',
                'requirement' => 'Level 5+',
                'icon' => 'fa-solid fa-radiation',
            ],
            [
                'id' => 'Mokerslag van de Afdeling',
                'name' => 'Mokerslag van de Afdeling',
                'unlocked' => $this->fights_won >= 10,
                'description' => 'Niemand daagt jou ongestraft uit (10+ gevechten gewonnen).',
                'requirement' => '10+ Gevechten gewonnen',
                'icon' => 'fa-solid fa-hammer',
            ],
            [
                'id' => 'Kliniek Bankier',
                'name' => 'Kliniek Bankier',
                'unlocked' => $totalWealth >= 50000,
                'description' => 'Beheert het zwarte geld van de hele inrichting (€50.000+ vermogen).',
                'requirement' => '€50.000+ Vermogen',
                'icon' => 'fa-solid fa-vault',
            ],
            [
                'id' => 'Bende Kopstuk',
                'name' => 'Bende Kopstuk',
                'unlocked' => ! empty($this->gang_id),
                'description' => 'Heeft de rugdekking van een georganiseerde bende.',
                'requirement' => 'Lid van een bende',
                'icon' => 'fa-solid fa-users-viewfinder',
            ],
            [
                'id' => 'Kliniek Psychopaat',
                'name' => 'Kliniek Psychopaat',
                'unlocked' => $this->level >= 10,
                'description' => 'Een kille legende binnen de muren van de kliniek (Level 10+).',
                'requirement' => 'Level 10+',
                'icon' => 'fa-solid fa-skull',
            ],
            [
                'id' => 'Vluchtgevaarlijk (Code Rood)',
                'name' => 'Vluchtgevaarlijk (Code Rood)',
                'unlocked' => $this->level >= 15 || $this->prison_escapes >= 5,
                'description' => 'Staat onder continue camerabewaking (Level 15+ of 5 ontsnappingen).',
                'requirement' => 'Level 15+ of 5 ontsnappingen',
                'icon' => 'fa-solid fa-triangle-exclamation',
            ],
        ];

        if ($this->user?->role === 'owner' || $this->user?->role === 'admin' || $this->user?->is_staff || $this->title === 'Eigenaar') {
            array_unshift($titles, [
                'id' => 'Eigenaar',
                'name' => '👑 Kliniek Eigenaar & Leiding',
                'unlocked' => true,
                'description' => 'Opperbevelhebber en beheerder van de TBS Kliniek.',
                'requirement' => 'Kliniek Leiding',
                'icon' => 'fa-solid fa-crown',
            ]);
        }

        if (! empty($this->title) && ! collect($titles)->contains('id', $this->title)) {
            array_unshift($titles, [
                'id' => $this->title,
                'name' => $this->title,
                'unlocked' => true,
                'description' => 'Exclusieve toegekende eretitel.',
                'requirement' => 'Speciale Eretitel',
                'icon' => 'fa-solid fa-star',
            ]);
        }

        return $titles;
    }

    /**
     * Get definitions for all achievement medal sets.
     *
     * @return array<string, array{id: string, name: string, icon: string, description: string}>
     */
    public static function getBadgeSets(): array
    {
        return [
            'inrichting' => [
                'id' => 'inrichting',
                'name' => 'Inrichting & Dossier',
                'icon' => 'fa-solid fa-hospital-user',
                'description' => 'Officiële dossiers, rangen en institutionele reputatie in de TBS kliniek.',
            ],
            'luchtplaats' => [
                'id' => 'luchtplaats',
                'name' => 'Luchtplaats & Gevechten',
                'icon' => 'fa-solid fa-hand-fist',
                'description' => 'Geweld, gevechten en heerschappij tijdens het dagelijkse luchten.',
            ],
            'misdaad' => [
                'id' => 'misdaad',
                'name' => 'Misdaad & Boevenpad',
                'icon' => 'fa-solid fa-mask',
                'description' => 'Inbraak, diefstal, smokkel en het ontlopen van isolatiecellen.',
            ],
            'rijkdom' => [
                'id' => 'rijkdom',
                'name' => 'Zwart Geld & Financiën',
                'icon' => 'fa-solid fa-sack-dollar',
                'description' => 'Smokkelwinsten, witwassen en de rijkste bajesbankrekening.',
            ],
            'broederschap' => [
                'id' => 'broederschap',
                'name' => 'Broederschap & Netwerk',
                'icon' => 'fa-solid fa-users',
                'description' => 'Allianties, trouwe vrienden, bendeconnecties en invloed op de afdeling.',
            ],
            'missies' => [
                'id' => 'missies',
                'name' => 'Missies & Discipline',
                'icon' => 'fa-solid fa-bullseye',
                'description' => 'Klusjes voor bewakers, speciale operaties en strak regime.',
            ],
        ];
    }

    /**
     * Get profile achievements and medals with unlock status and progress.
     *
     * @return list<array{id: string, name: string, set_id: string, set_name: string, set_icon: string, tier: string, tier_name: string, tier_stars: int, asset_id: string, icon: string, color: string, description: string, unlocked: bool, progress: string, progress_pct: int}>
     */
    public function getBadges(): array
    {
        $totalWealth = $this->cash + $this->bank;
        $hasFriend = Friend::where('character_id', $this->id)->where('status', 'accepted')->exists();
        $friendsCount = Friend::where('character_id', $this->id)->where('status', 'accepted')->count();
        $referralsCount = Referral::where('referrer_id', $this->id)->count();

        return [
            // --- SET 1: INRICHTING & DOSSIER ---
            [
                'id' => 'first_intake',
                'name' => 'Officiële Opname',
                'set_id' => 'inrichting',
                'set_name' => 'Inrichting & Dossier',
                'set_icon' => 'fa-solid fa-hospital-user',
                'tier' => 'bronze',
                'tier_name' => 'Brons',
                'tier_stars' => 1,
                'asset_id' => 'intake',
                'icon' => 'fa-solid fa-file-signature',
                'color' => 'from-amber-600 to-amber-700',
                'description' => 'Geregistreerd in het officiële dossier van de TBS Kliniek.',
                'unlocked' => true,
                'progress' => 'Voltooid',
                'progress_pct' => 100,
            ],
            [
                'id' => 'veteran',
                'name' => 'Kliniek Veteraan',
                'set_id' => 'inrichting',
                'set_name' => 'Inrichting & Dossier',
                'set_icon' => 'fa-solid fa-hospital-user',
                'tier' => 'silver',
                'tier_name' => 'Zilver',
                'tier_stars' => 2,
                'asset_id' => 'veteran',
                'icon' => 'fa-solid fa-trophy',
                'color' => 'from-slate-400 to-slate-600',
                'description' => 'Bereik Level 10 of hoger in de inrichting.',
                'unlocked' => $this->level >= 10,
                'progress' => 'Lvl '.$this->level.' / 10',
                'progress_pct' => min(100, (int) round(($this->level / 10) * 100)),
            ],
            [
                'id' => 'legend',
                'name' => 'Afdelings Legende',
                'set_id' => 'inrichting',
                'set_name' => 'Inrichting & Dossier',
                'set_icon' => 'fa-solid fa-hospital-user',
                'tier' => 'gold',
                'tier_name' => 'Goud',
                'tier_stars' => 3,
                'asset_id' => 'legend',
                'icon' => 'fa-solid fa-crown',
                'color' => 'from-yellow-400 to-amber-600',
                'description' => 'Bereik Level 25 en word een legendarische naam op de afdeling.',
                'unlocked' => $this->level >= 25,
                'progress' => 'Lvl '.$this->level.' / 25',
                'progress_pct' => min(100, (int) round(($this->level / 25) * 100)),
            ],
            [
                'id' => 'respect_lord',
                'name' => 'Onbetwist Respect',
                'set_id' => 'inrichting',
                'set_name' => 'Inrichting & Dossier',
                'set_icon' => 'fa-solid fa-hospital-user',
                'tier' => 'diamond',
                'tier_name' => 'Diamant',
                'tier_stars' => 4,
                'asset_id' => 'respect',
                'icon' => 'fa-solid fa-heart-pulse',
                'color' => 'from-cyan-400 to-blue-600',
                'description' => 'Verzamel minimaal 500 respectpunten onder medepatiënten.',
                'unlocked' => $this->respect >= 500,
                'progress' => min(500, $this->respect).' / 500 respect',
                'progress_pct' => min(100, (int) round(($this->respect / 500) * 100)),
            ],

            // --- SET 2: LUCHTPLAATS & GEVECHTEN ---
            [
                'id' => 'brawler',
                'name' => 'Luchtplaats Kampioen',
                'set_id' => 'luchtplaats',
                'set_name' => 'Luchtplaats & Gevechten',
                'set_icon' => 'fa-solid fa-hand-fist',
                'tier' => 'bronze',
                'tier_name' => 'Brons',
                'tier_stars' => 1,
                'asset_id' => 'brawler',
                'icon' => 'fa-solid fa-hand-fist',
                'color' => 'from-rose-500 to-red-600',
                'description' => 'Win minimaal 5 gevechten op de luchtplaats.',
                'unlocked' => $this->fights_won >= 5,
                'progress' => min(5, $this->fights_won).' / 5 gevechten',
                'progress_pct' => min(100, (int) round(($this->fights_won / 5) * 100)),
            ],
            [
                'id' => 'gladiator',
                'name' => 'Gladiator van de Kliniek',
                'set_id' => 'luchtplaats',
                'set_name' => 'Luchtplaats & Gevechten',
                'set_icon' => 'fa-solid fa-hand-fist',
                'tier' => 'silver',
                'tier_name' => 'Zilver',
                'tier_stars' => 2,
                'asset_id' => 'gladiator',
                'icon' => 'fa-solid fa-shield-halved',
                'color' => 'from-blue-500 to-indigo-600',
                'description' => 'Win 20 gevechten tegen medepatiënten.',
                'unlocked' => $this->fights_won >= 20,
                'progress' => min(20, $this->fights_won).' / 20 gevechten',
                'progress_pct' => min(100, (int) round(($this->fights_won / 20) * 100)),
            ],
            [
                'id' => 'executioner',
                'name' => 'Bajes Beul',
                'set_id' => 'luchtplaats',
                'set_name' => 'Luchtplaats & Gevechten',
                'set_icon' => 'fa-solid fa-hand-fist',
                'tier' => 'gold',
                'tier_name' => 'Goud',
                'tier_stars' => 3,
                'asset_id' => 'executioner',
                'icon' => 'fa-solid fa-skull',
                'color' => 'from-amber-500 to-orange-600',
                'description' => 'Win minimaal 50 gevechten en boezem ontzag in op de luchtplaats.',
                'unlocked' => $this->fights_won >= 50,
                'progress' => min(50, $this->fights_won).' / 50 gevechten',
                'progress_pct' => min(100, (int) round(($this->fights_won / 50) * 100)),
            ],
            [
                'id' => 'iron_eater',
                'name' => 'IJzervreter',
                'set_id' => 'luchtplaats',
                'set_name' => 'Luchtplaats & Gevechten',
                'set_icon' => 'fa-solid fa-hand-fist',
                'tier' => 'diamond',
                'tier_name' => 'Diamant',
                'tier_stars' => 4,
                'asset_id' => 'strength',
                'icon' => 'fa-solid fa-dumbbell',
                'color' => 'from-cyan-500 to-teal-600',
                'description' => 'Kweek minimaal 100 spierkracht in het krachthonk.',
                'unlocked' => $this->strength >= 100,
                'progress' => min(100, $this->strength).' / 100 kracht',
                'progress_pct' => min(100, (int) round(($this->strength / 100) * 100)),
            ],

            // --- SET 3: MISDAAD & BOEVENPAD ---
            [
                'id' => 'pickpocket',
                'name' => 'Vlugge Vingers',
                'set_id' => 'misdaad',
                'set_name' => 'Misdaad & Boevenpad',
                'set_icon' => 'fa-solid fa-mask',
                'tier' => 'bronze',
                'tier_name' => 'Brons',
                'tier_stars' => 1,
                'asset_id' => 'pickpocket',
                'icon' => 'fa-solid fa-hand',
                'color' => 'from-emerald-500 to-teal-600',
                'description' => 'Voer minimaal 10 succesvolle diefstallen of zakkenrollerijen uit.',
                'unlocked' => ($this->thefts_succeeded ?? 0) >= 10,
                'progress' => min(10, $this->thefts_succeeded ?? 0).' / 10 diefstallen',
                'progress_pct' => min(100, (int) round((($this->thefts_succeeded ?? 0) / 10) * 100)),
            ],
            [
                'id' => 'master_burglar',
                'name' => 'Meester Inbreker',
                'set_id' => 'misdaad',
                'set_name' => 'Misdaad & Boevenpad',
                'set_icon' => 'fa-solid fa-mask',
                'tier' => 'silver',
                'tier_name' => 'Zilver',
                'tier_stars' => 2,
                'asset_id' => 'burglar',
                'icon' => 'fa-solid fa-key',
                'color' => 'from-blue-500 to-cyan-600',
                'description' => 'Kraak 30 keer succesvol spullen van anderen of kluizen.',
                'unlocked' => ($this->thefts_succeeded ?? 0) >= 30,
                'progress' => min(30, $this->thefts_succeeded ?? 0).' / 30 diefstallen',
                'progress_pct' => min(100, (int) round((($this->thefts_succeeded ?? 0) / 30) * 100)),
            ],
            [
                'id' => 'shadow_walker',
                'name' => 'Kliniek Schaduw',
                'set_id' => 'misdaad',
                'set_name' => 'Misdaad & Boevenpad',
                'set_icon' => 'fa-solid fa-mask',
                'tier' => 'gold',
                'tier_name' => 'Goud',
                'tier_stars' => 3,
                'asset_id' => 'shadow',
                'icon' => 'fa-solid fa-user-secret',
                'color' => 'from-yellow-500 to-amber-600',
                'description' => 'Voer minimaal 75 succesvolle misdaden uit zonder sporen achter te laten.',
                'unlocked' => ($this->thefts_succeeded ?? 0) >= 75,
                'progress' => min(75, $this->thefts_succeeded ?? 0).' / 75 misdaden',
                'progress_pct' => min(100, (int) round((($this->thefts_succeeded ?? 0) / 75) * 100)),
            ],
            [
                'id' => 'escape_artist',
                'name' => 'Bivak Meester',
                'set_id' => 'misdaad',
                'set_name' => 'Misdaad & Boevenpad',
                'set_icon' => 'fa-solid fa-mask',
                'tier' => 'diamond',
                'tier_name' => 'Diamant',
                'tier_stars' => 4,
                'asset_id' => 'mask',
                'icon' => 'fa-solid fa-mask',
                'color' => 'from-cyan-400 to-emerald-600',
                'description' => 'Pleeg minimaal 150 misdaden of ontsnap succesvol uit isolatie.',
                'unlocked' => (($this->thefts_succeeded ?? 0) >= 150) || (($this->prison_escapes ?? 0) >= 1),
                'progress' => min(150, $this->thefts_succeeded ?? 0).' / 150 acties',
                'progress_pct' => min(100, (int) round((($this->thefts_succeeded ?? 0) / 150) * 100)),
            ],

            // --- SET 4: ZWART GELD & FINANCIËN ---
            [
                'id' => 'pennypincher',
                'name' => 'Kruimelspaarder',
                'set_id' => 'rijkdom',
                'set_name' => 'Zwart Geld & Financiën',
                'set_icon' => 'fa-solid fa-sack-dollar',
                'tier' => 'bronze',
                'tier_name' => 'Brons',
                'tier_stars' => 1,
                'asset_id' => 'money_bronze',
                'icon' => 'fa-solid fa-money-bill-wave',
                'color' => 'from-emerald-500 to-green-600',
                'description' => 'Verzamel een gecombineerd vermogen van minimaal € 10.000.',
                'unlocked' => $totalWealth >= 10000,
                'progress' => '€'.number_format($totalWealth, 0, ',', '.').' / €10.000',
                'progress_pct' => min(100, (int) round(($totalWealth / 10000) * 100)),
            ],
            [
                'id' => 'wealthy',
                'name' => 'Zwartgeld Tycoon',
                'set_id' => 'rijkdom',
                'set_name' => 'Zwart Geld & Financiën',
                'set_icon' => 'fa-solid fa-sack-dollar',
                'tier' => 'silver',
                'tier_name' => 'Zilver',
                'tier_stars' => 2,
                'asset_id' => 'tycoon',
                'icon' => 'fa-solid fa-coins',
                'color' => 'from-yellow-400 to-amber-600',
                'description' => 'Verzamel een gecombineerd vermogen van minimaal € 50.000.',
                'unlocked' => $totalWealth >= 50000,
                'progress' => '€'.number_format($totalWealth, 0, ',', '.').' / €50.000',
                'progress_pct' => min(100, (int) round(($totalWealth / 50000) * 100)),
            ],
            [
                'id' => 'millionaire',
                'name' => 'Kliniek Miljonair',
                'set_id' => 'rijkdom',
                'set_name' => 'Zwart Geld & Financiën',
                'set_icon' => 'fa-solid fa-sack-dollar',
                'tier' => 'gold',
                'tier_name' => 'Goud',
                'tier_stars' => 3,
                'asset_id' => 'millionaire',
                'icon' => 'fa-solid fa-gem',
                'color' => 'from-violet-500 to-fuchsia-600',
                'description' => 'Bereik een vermogen van minimaal € 1.000.000.',
                'unlocked' => $totalWealth >= 1000000,
                'progress' => '€'.number_format($totalWealth, 0, ',', '.').' / €1.000.000',
                'progress_pct' => min(100, (int) round(($totalWealth / 1000000) * 100)),
            ],
            [
                'id' => 'oligarch',
                'name' => 'Oligarch van de Afdeling',
                'set_id' => 'rijkdom',
                'set_name' => 'Zwart Geld & Financiën',
                'set_icon' => 'fa-solid fa-sack-dollar',
                'tier' => 'diamond',
                'tier_name' => 'Diamant',
                'tier_stars' => 4,
                'asset_id' => 'oligarch',
                'icon' => 'fa-solid fa-landmark',
                'color' => 'from-cyan-400 to-blue-600',
                'description' => 'Bezit minimaal € 5.000.000 zwart geld en heers over de kliniekeconomie.',
                'unlocked' => $totalWealth >= 5000000,
                'progress' => '€'.number_format($totalWealth, 0, ',', '.').' / €5.000.000',
                'progress_pct' => min(100, (int) round(($totalWealth / 5000000) * 100)),
            ],

            // --- SET 5: BROEDERSCHAP & NETWERK ---
            [
                'id' => 'brotherhood',
                'name' => 'Kameraadschap',
                'set_id' => 'broederschap',
                'set_name' => 'Broederschap & Netwerk',
                'set_icon' => 'fa-solid fa-users',
                'tier' => 'bronze',
                'tier_name' => 'Brons',
                'tier_stars' => 1,
                'asset_id' => 'friend',
                'icon' => 'fa-solid fa-user-group',
                'color' => 'from-pink-500 to-rose-600',
                'description' => 'Sluit vriendschap met minimaal één medepatiënt.',
                'unlocked' => $hasFriend,
                'progress' => $hasFriend ? 'Voltooid' : '0 / 1 vriend',
                'progress_pct' => $hasFriend ? 100 : 0,
            ],
            [
                'id' => 'social_butterfly',
                'name' => 'Gezelligheidsdier',
                'set_id' => 'broederschap',
                'set_name' => 'Broederschap & Netwerk',
                'set_icon' => 'fa-solid fa-users',
                'tier' => 'silver',
                'tier_name' => 'Zilver',
                'tier_stars' => 2,
                'asset_id' => 'network',
                'icon' => 'fa-solid fa-comments',
                'color' => 'from-blue-500 to-indigo-600',
                'description' => 'Bouw een netwerk op van minimaal 3 geaccepteerde vrienden.',
                'unlocked' => $friendsCount >= 3,
                'progress' => min(3, $friendsCount).' / 3 vrienden',
                'progress_pct' => min(100, (int) round(($friendsCount / 3) * 100)),
            ],
            [
                'id' => 'gang',
                'name' => 'Bende Broederschap',
                'set_id' => 'broederschap',
                'set_name' => 'Broederschap & Netwerk',
                'set_icon' => 'fa-solid fa-users',
                'tier' => 'gold',
                'tier_name' => 'Goud',
                'tier_stars' => 3,
                'asset_id' => 'gang',
                'icon' => 'fa-solid fa-skull-crossbones',
                'color' => 'from-slate-700 to-slate-900',
                'description' => 'Sluit je aan bij een bende of richt er zelf een op.',
                'unlocked' => ! empty($this->gang_id),
                'progress' => ! empty($this->gang_id) ? 'Voltooid' : 'Nog geen bende',
                'progress_pct' => ! empty($this->gang_id) ? 100 : 0,
            ],
            [
                'id' => 'recruiter',
                'name' => 'Kliniek Recruiter',
                'set_id' => 'broederschap',
                'set_name' => 'Broederschap & Netwerk',
                'set_icon' => 'fa-solid fa-users',
                'tier' => 'diamond',
                'tier_name' => 'Diamant',
                'tier_stars' => 4,
                'asset_id' => 'recruiter',
                'icon' => 'fa-solid fa-bullhorn',
                'color' => 'from-cyan-400 to-purple-600',
                'description' => 'Breng nieuwe spelers aan via je referral link of bezit 50 charisma.',
                'unlocked' => ($referralsCount >= 1) || ($this->charisma >= 50),
                'progress' => $referralsCount > 0 ? ($referralsCount.' referrals') : ($this->charisma.' / 50 charisma'),
                'progress_pct' => ($referralsCount >= 1 || $this->charisma >= 50) ? 100 : min(100, (int) round(($this->charisma / 50) * 100)),
            ],

            // --- SET 6: MISSIES & DISCIPLINE ---
            [
                'id' => 'handyman',
                'name' => 'Klusjesman',
                'set_id' => 'missies',
                'set_name' => 'Missies & Discipline',
                'set_icon' => 'fa-solid fa-bullseye',
                'tier' => 'bronze',
                'tier_name' => 'Brons',
                'tier_stars' => 1,
                'asset_id' => 'task',
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'color' => 'from-amber-500 to-orange-600',
                'description' => 'Voltooi minimaal 3 klusjes en opdrachten in de inrichting.',
                'unlocked' => $this->missions_completed >= 3,
                'progress' => min(3, $this->missions_completed).' / 3 missies',
                'progress_pct' => min(100, (int) round(($this->missions_completed / 3) * 100)),
            ],
            [
                'id' => 'missions',
                'name' => 'Missie Meester',
                'set_id' => 'missies',
                'set_name' => 'Missies & Discipline',
                'set_icon' => 'fa-solid fa-bullseye',
                'tier' => 'silver',
                'tier_name' => 'Zilver',
                'tier_stars' => 2,
                'asset_id' => 'mission_master',
                'icon' => 'fa-solid fa-bullseye',
                'color' => 'from-blue-500 to-cyan-600',
                'description' => 'Voltooi minimaal 10 kliniek opdrachten & missies.',
                'unlocked' => $this->missions_completed >= 10,
                'progress' => min(10, $this->missions_completed).' / 10 missies',
                'progress_pct' => min(100, (int) round(($this->missions_completed / 10) * 100)),
            ],
            [
                'id' => 'commander',
                'name' => 'Operatie Commandeur',
                'set_id' => 'missies',
                'set_name' => 'Missies & Discipline',
                'set_icon' => 'fa-solid fa-bullseye',
                'tier' => 'gold',
                'tier_name' => 'Goud',
                'tier_stars' => 3,
                'asset_id' => 'commander',
                'icon' => 'fa-solid fa-award',
                'color' => 'from-yellow-400 to-amber-600',
                'description' => 'Voltooi 25 missies en bewijs je tactische leiding.',
                'unlocked' => $this->missions_completed >= 25,
                'progress' => min(25, $this->missions_completed).' / 25 missies',
                'progress_pct' => min(100, (int) round(($this->missions_completed / 25) * 100)),
            ],
            [
                'id' => 'discipline_iron',
                'name' => 'IJzeren Discipline',
                'set_id' => 'missies',
                'set_name' => 'Missies & Discipline',
                'set_icon' => 'fa-solid fa-bullseye',
                'tier' => 'diamond',
                'tier_name' => 'Diamant',
                'tier_stars' => 4,
                'asset_id' => 'discipline',
                'icon' => 'fa-solid fa-stopwatch-20',
                'color' => 'from-cyan-400 to-indigo-600',
                'description' => 'Behaal 100 discipline of voltooi 50 missies zonder wankelen.',
                'unlocked' => ($this->discipline >= 100) || ($this->missions_completed >= 50),
                'progress' => min(50, $this->missions_completed).' / 50 missies',
                'progress_pct' => min(100, (int) round(($this->missions_completed / 50) * 100)),
            ],
        ];
    }

    /**
     * Generate dynamic official psychiatric evaluation report from Dr. Van Beek.
     *
     * @return array{dossier_nr: string, threat_level: string, threat_color: string, threat_icon: string, danger_desc: string, diagnosis: string, doctor_notes: string, treatment_plan: string, doctor_name: string, doctor_role: string}
     */
    public function getPsychiatricReport(): array
    {
        $fights = $this->fights_won;
        $level = $this->level;
        $wealth = $this->cash + $this->bank;

        if ($fights >= 35 || $level >= 20) {
            $threatLevel = 'Code Zwart';
            $threatColor = 'bg-slate-900 dark:bg-slate-950 text-rose-400 border-rose-600/80 shadow-lg shadow-rose-950/30';
            $threatBadgeColor = 'bg-rose-500/20 text-rose-300 border-rose-500/50';
            $threatIcon = 'fa-solid fa-biohazard text-rose-400 animate-pulse';
            $dangerDesc = 'EXTREEM GEVAARLIJK. Maximale penitentiaire isolatie. Verhoogd ontsnappings- en gijzelingsrisico.';
            $threatMeter = 100;
            $regime = 'EBI - Extra Beveiligde Inrichting';
        } elseif ($fights >= 15 || $level >= 10) {
            $threatLevel = 'Code Rood';
            $threatColor = 'bg-rose-50 dark:bg-rose-950/70 text-rose-900 dark:text-rose-200 border-rose-300 dark:border-rose-800 shadow-xs';
            $threatBadgeColor = 'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 border-rose-300 dark:border-rose-700';
            $threatIcon = 'fa-solid fa-skull-crossbones text-rose-600 dark:text-rose-400';
            $dangerDesc = 'Uiterst gewelddadig en dominant. Vermijd direct oogcontact tijdens recreatie en luchtuurtjes.';
            $threatMeter = 80;
            $regime = 'Afdeling Intensief Toezicht (AIT)';
        } elseif ($fights >= 5 || $level >= 5) {
            $threatLevel = 'Code Oranje';
            $threatColor = 'bg-amber-50 dark:bg-amber-950/70 text-amber-900 dark:text-amber-200 border-amber-300 dark:border-amber-800 shadow-xs';
            $threatBadgeColor = 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 border-amber-300 dark:border-amber-700';
            $threatIcon = 'fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400';
            $dangerDesc = 'Verhoogde waakzaamheid geboden. Vertoont manipulatief, territoriaal en opstandig gedrag.';
            $threatMeter = 55;
            $regime = 'Gesloten Behandelafdeling';
        } elseif ($level >= 2) {
            $threatLevel = 'Code Geel';
            $threatColor = 'bg-yellow-50 dark:bg-yellow-950/70 text-yellow-900 dark:text-yellow-200 border-yellow-300 dark:border-yellow-800 shadow-xs';
            $threatBadgeColor = 'bg-yellow-100 dark:bg-yellow-900/60 text-yellow-800 dark:text-yellow-200 border-yellow-300 dark:border-yellow-700';
            $threatIcon = 'fa-solid fa-circle-exclamation text-yellow-600 dark:text-yellow-400';
            $dangerDesc = 'Matig risico. Patiënt past zich ogenschijnlijk aan de regels aan, maar beraamt plannen in de schaduw.';
            $threatMeter = 30;
            $regime = 'Regulier Gesloten Regime';
        } else {
            $threatLevel = 'Code Groen';
            $threatColor = 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-900 dark:text-emerald-200 border-emerald-300 dark:border-emerald-800 shadow-xs';
            $threatBadgeColor = 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border-emerald-300 dark:border-emerald-700';
            $threatIcon = 'fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400';
            $dangerDesc = 'Nieuwe intake. Observatieperiode loopt nog; vooralsnog beheersbaar binnen de leefgroep.';
            $threatMeter = 10;
            $regime = 'Intake & Observatiegroep';
        }

        if ($wealth >= 100000) {
            $dsmCode = 'DSM-TBS 301.81 / F60.8';
            $diagnosis = 'Witteboorden-paranoia & Illegaal Vermogensbeheer';
            $notes = 'Patiënt vertoont opvallende obsessie met zwarte geldstromen, sigarettenhandel en omkoping van mede-gedetineerden. Vermoedelijk spilfiguur in illegale smokkelnetwerken binnen de inrichting.';
            $treatment = 'Regelmatige inspectie van celkluisjes en smokkelroutes; maximaal € 50 zakgeld per week toegestaan op de kliniekrekening.';
            $medication = '15mg Oxazepam p.n. bij financiële onrust; strikte financiële curatele.';
        } elseif ($fights >= 5) {
            $dsmCode = 'DSM-TBS 312.34 / F63.81';
            $diagnosis = 'Chronische Impuls-beheersingsstoornis & Luchtplaats-agressie';
            $notes = 'Sterk ontwikkelde fysieke drang tot territoriumdrift en dominantie. Zoekt actief confrontaties op met mede-inwoners tijdens het luchtuurtje en krachttraining.';
            $treatment = '200mg Kalmeringstabletten per dag; verplichte woedebeheersingstherapie en extra bewaking bij de gewichten en het recreatieplein.';
            $medication = '200mg Haloperidol depot 1x p/w + Stemmingsstabilisatoren.';
        } elseif (($this->thefts_succeeded ?? 0) >= 5) {
            $dsmCode = 'DSM-TBS 312.32 / F63.2';
            $diagnosis = 'Compulsieve Kleptomanie & Georganiseerde Beroepscriminaliteit';
            $notes = 'Kan andermans eigendommen absoluut niet met rust laten. Beweegt zich geruisloos over de gangen en ontvreemdt systematisch persoonlijke bezittingen en gereedschappen.';
            $treatment = 'Grondige fouillering bij het betreden en verlaten van gemeenschappelijke ruimtes; metaaldetectie bij celdeuren.';
            $medication = '50mg Sertraline '.'s ochtends ter vermindering van compulsieve drang.';
        } else {
            $dsmCode = 'DSM-TBS 301.7 / F60.2';
            $diagnosis = 'Anti-sociale Persoonlijkheidsstructuur (Type TBS-1)';
            $notes = 'Vertoont typisch kliniekgedrag: zoekt allianties, bouwt reputatie op en probeert de regels van de inrichting naar eigen hand te zetten.';
            $treatment = 'Wekelijkse één-op-één sessies met psychiater Dr. Van Beek; deelname aan kliniekactiviteiten en dagbesteding stimuleren.';
            $medication = 'Geen zware medicatie geïndiceerd; gedragsmonitoring in teamoverleg.';
        }

        return [
            'dossier_nr' => 'TBS-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
            'threat_level' => $threatLevel,
            'threat_color' => $threatColor,
            'threat_badge_color' => $threatBadgeColor,
            'threat_icon' => $threatIcon,
            'danger_desc' => $dangerDesc,
            'threat_meter' => $threatMeter,
            'regime' => $regime,
            'dsm_code' => $dsmCode,
            'diagnosis' => $diagnosis,
            'doctor_notes' => $notes,
            'treatment_plan' => $treatment,
            'medication' => $medication,
            'doctor_name' => 'Dr. H. van Beek',
            'doctor_role' => 'Hoofd Behandelend Geneesheer Forensische Zorg',
            'big_registration' => 'BIG: 492.019.481',
        ];
    }

    /**
     * @return HasOne<CharacterArmWrestleStat, $this>
     */
    public function armWrestleStat(): HasOne
    {
        return $this->hasOne(CharacterArmWrestleStat::class);
    }

    /**
     * @return HasMany<CharacterVehicle, $this>
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(CharacterVehicle::class);
    }

    /**
     * @return HasMany<CharacterItem, $this>
     */
    public function characterItems(): HasMany
    {
        return $this->hasMany(CharacterItem::class)->where('quantity', '>', 0);
    }

    /**
     * Maximum number of vehicles allowed in personal garage based on level and VIP status.
     */
    public function getGarageCapacity(): int
    {
        $lvl = $this->level ?? 1;

        if ($lvl >= 50) {
            $cap = 6;
        } elseif ($lvl >= 30) {
            $cap = 5;
        } elseif ($lvl >= 20) {
            $cap = 4;
        } elseif ($lvl >= 10) {
            $cap = 3;
        } else {
            $cap = 2;
        }

        if ($this->isVip()) {
            $cap += 3;
        }

        return $cap;
    }

    /**
     * Check if the character is in Intensive Care Coma.
     */
    public function isComatose(): bool
    {
        return $this->coma_until !== null && $this->coma_until->isFuture();
    }

    /**
     * Remaining seconds in Intensive Care Coma.
     */
    public function getComaRemainingSeconds(): int
    {
        if (! $this->isComatose()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->coma_until, false));
    }

    /**
     * Check if the character has an active bulletproof vest.
     */
    public function hasVest(): bool
    {
        return $this->vest_until !== null && $this->vest_until->isFuture();
    }

    /**
     * Remaining seconds of bulletproof vest protection.
     */
    public function getVestRemainingSeconds(): int
    {
        if (! $this->hasVest()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->vest_until, false));
    }

    public const REVIVE_SERUM_NAME = 'Forensisch Reanimatie-Serum (Epinefrine-Ampul)';

    public const REVIVE_SERUM_CASH = 1500000;

    public const REVIVE_SERUM_STARS = 75;

    public const MAX_LIQUIDATIONS_BEFORE_SERUM = 10;

    /**
     * Catalog of the 30 TBS Forensic & Syndicate Dossier Grades.
     *
     * @return array<int, array{grade: int, title: string, phase: string, min_bullets: int, can_one_tap: bool, armor_pierce: int}>
     */
    public static function syndicateGradesCatalog(): array
    {
        return [
            1 => ['grade' => 1, 'title' => 'Observatiepatiënt', 'phase' => 'Fase I: Observatie & Loopwerk', 'min_bullets' => 3200, 'can_one_tap' => false, 'armor_pierce' => 0],
            2 => ['grade' => 2, 'title' => 'Intake-Recruut', 'phase' => 'Fase I: Observatie & Loopwerk', 'min_bullets' => 2900, 'can_one_tap' => false, 'armor_pierce' => 0],
            3 => ['grade' => 3, 'title' => 'Gangpad-Loopjongen', 'phase' => 'Fase I: Observatie & Loopwerk', 'min_bullets' => 2600, 'can_one_tap' => false, 'armor_pierce' => 0],
            4 => ['grade' => 4, 'title' => 'Celblok-Verkenner', 'phase' => 'Fase I: Observatie & Loopwerk', 'min_bullets' => 2300, 'can_one_tap' => false, 'armor_pierce' => 0],
            5 => ['grade' => 5, 'title' => 'Paviljoen-Ronselaar', 'phase' => 'Fase I: Observatie & Loopwerk', 'min_bullets' => 2000, 'can_one_tap' => false, 'armor_pierce' => 2],
            6 => ['grade' => 6, 'title' => 'Celblok-Intimidator', 'phase' => 'Fase II: Celblok Handhaving', 'min_bullets' => 1750, 'can_one_tap' => false, 'armor_pierce' => 4],
            7 => ['grade' => 7, 'title' => 'Kantine-Afperser', 'phase' => 'Fase II: Celblok Handhaving', 'min_bullets' => 1500, 'can_one_tap' => false, 'armor_pierce' => 5],
            8 => ['grade' => 8, 'title' => 'Luchtplaats-Handhaver', 'phase' => 'Fase II: Celblok Handhaving', 'min_bullets' => 1300, 'can_one_tap' => false, 'armor_pierce' => 6],
            9 => ['grade' => 9, 'title' => 'Afdelings-Breker', 'phase' => 'Fase II: Celblok Handhaving', 'min_bullets' => 1150, 'can_one_tap' => false, 'armor_pierce' => 8],
            10 => ['grade' => 10, 'title' => 'Nachtdienst-Schrik', 'phase' => 'Fase II: Celblok Handhaving', 'min_bullets' => 1000, 'can_one_tap' => false, 'armor_pierce' => 10],
            11 => ['grade' => 11, 'title' => 'Smokkel-Coördinator', 'phase' => 'Fase III: Paviljoen Commando', 'min_bullets' => 880, 'can_one_tap' => false, 'armor_pierce' => 12],
            12 => ['grade' => 12, 'title' => 'Kluis-Manipulator', 'phase' => 'Fase III: Paviljoen Commando', 'min_bullets' => 760, 'can_one_tap' => false, 'armor_pierce' => 14],
            13 => ['grade' => 13, 'title' => 'Paviljoen-Strateeg', 'phase' => 'Fase III: Paviljoen Commando', 'min_bullets' => 650, 'can_one_tap' => false, 'armor_pierce' => 15],
            14 => ['grade' => 14, 'title' => 'Binnenplaats-Veldheer', 'phase' => 'Fase III: Paviljoen Commando', 'min_bullets' => 560, 'can_one_tap' => false, 'armor_pierce' => 16],
            15 => ['grade' => 15, 'title' => 'Isolatie-Overlever', 'phase' => 'Fase III: Paviljoen Commando', 'min_bullets' => 490, 'can_one_tap' => false, 'armor_pierce' => 18],
            16 => ['grade' => 16, 'title' => 'Paviljoen-Commandant', 'phase' => 'Fase III: Paviljoen Commando', 'min_bullets' => 420, 'can_one_tap' => false, 'armor_pierce' => 20],
            17 => ['grade' => 17, 'title' => 'Schaduw-Operateur', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 360, 'can_one_tap' => false, 'armor_pierce' => 22],
            18 => ['grade' => 18, 'title' => 'Stille Informant-Jager', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 310, 'can_one_tap' => false, 'armor_pierce' => 24],
            19 => ['grade' => 19, 'title' => 'Forensisch Saboteur', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 270, 'can_one_tap' => false, 'armor_pierce' => 25],
            20 => ['grade' => 20, 'title' => 'Dossier-Wisser', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 235, 'can_one_tap' => false, 'armor_pierce' => 26],
            21 => ['grade' => 21, 'title' => 'Kliniek-Infiltrant', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 200, 'can_one_tap' => false, 'armor_pierce' => 28],
            22 => ['grade' => 22, 'title' => 'Onderwereld-Aanklager', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 175, 'can_one_tap' => false, 'armor_pierce' => 30],
            23 => ['grade' => 23, 'title' => 'Nachtvleugel-Specialist', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 150, 'can_one_tap' => false, 'armor_pierce' => 32],
            24 => ['grade' => 24, 'title' => 'Kliniek-Examinator', 'phase' => 'Fase IV: Schaduw-Operaties', 'min_bullets' => 130, 'can_one_tap' => false, 'armor_pierce' => 35],
            25 => ['grade' => 25, 'title' => 'Forensisch Scherpschutter', 'phase' => 'Fase V: Scherpschutter (Genadeschot)', 'min_bullets' => 110, 'can_one_tap' => true, 'armor_pierce' => 40],
            26 => ['grade' => 26, 'title' => 'Tactisch Liquidateur', 'phase' => 'Fase V: Scherpschutter (Genadeschot)', 'min_bullets' => 95, 'can_one_tap' => true, 'armor_pierce' => 45],
            27 => ['grade' => 27, 'title' => 'Hoofd-Liquidateur', 'phase' => 'Fase V: Scherpschutter (Genadeschot)', 'min_bullets' => 80, 'can_one_tap' => true, 'armor_pierce' => 50],
            28 => ['grade' => 28, 'title' => 'Syndicaat-Chirurg', 'phase' => 'Fase VI: Code Zwart Directie', 'min_bullets' => 65, 'can_one_tap' => true, 'armor_pierce' => 55],
            29 => ['grade' => 29, 'title' => 'Schaduw-Directeur', 'phase' => 'Fase VI: Code Zwart Directie', 'min_bullets' => 50, 'can_one_tap' => true, 'armor_pierce' => 60],
            30 => ['grade' => 30, 'title' => 'Opper-Directeur (Code Zwart)', 'phase' => 'Fase VI: Code Zwart Directie', 'min_bullets' => 40, 'can_one_tap' => true, 'armor_pierce' => 65],
        ];
    }

    /**
     * Get the character's current TBS Dossier Grade (1 - 30).
     */
    public function getSyndicateGrade(): int
    {
        $perks = $this->perks ?? [];
        if (isset($perks['syndicate_grade_override'])) {
            return max(1, min(30, (int) $perks['syndicate_grade_override']));
        }

        $levelScore = (int) ($this->level ?? 1);
        $killsBonus = (int) floor(($this->kills_count ?? 0) / 2);

        return max(1, min(30, $levelScore + $killsBonus));
    }

    /**
     * Get full dossier grade info for this character.
     *
     * @return array{grade: int, title: string, phase: string, min_bullets: int, can_one_tap: bool, armor_pierce: int}
     */
    public function getSyndicateGradeInfo(): array
    {
        $catalog = self::syndicateGradesCatalog();
        $grade = $this->getSyndicateGrade();

        return $catalog[$grade] ?? $catalog[1];
    }

    /**
     * Get Koudbloedigheid / Kaliberfocus percentage (0 - 100%).
     */
    public function getColdBloodedPct(): int
    {
        $perks = $this->perks ?? [];
        if (! array_key_exists('cold_blooded_pct', $perks)) {
            return 35;
        }

        return max(0, min(100, (int) $perks['cold_blooded_pct']));
    }

    /**
     * Adjust Koudbloedigheid percentage and persist.
     */
    public function adjustColdBloodedPct(int $delta): int
    {
        $perks = $this->perks ?? [];
        $current = $this->getColdBloodedPct();
        $next = max(0, min(100, $current + $delta));
        $perks['cold_blooded_pct'] = $next;
        $this->perks = $perks;
        $this->save();

        return $next;
    }

    /**
     * Check if character can execute a 1-bullet precision shot (Genadeschot).
     * Requires Dossier-Graad >= 25 AND Koudbloedigheid >= 30%.
     */
    public function canOneTapExecute(): bool
    {
        return $this->getSyndicateGrade() >= 25 && $this->getColdBloodedPct() >= 30;
    }

    /**
     * Minimum bullets required to fire at a target right now.
     */
    public function getMinimumBulletsRequired(): int
    {
        if ($this->canOneTapExecute()) {
            return 1;
        }

        return $this->getSyndicateGradeInfo()['min_bullets'];
    }

    /**
     * Catalog of Black Market Backfire Vests.
     *
     * @return array<string, array{id: string, name: string, subtitle: string, hours: int, cost_cash: int, cost_stars: int, defense_bonus: int, backfire_chance: int, badge: string, icon: string}>
     */
    public static function vestCatalog(): array
    {
        return [
            'kevlar_1h' => [
                'id' => 'kevlar_1h',
                'name' => 'Licht Kevlar Ondervest',
                'subtitle' => '1 uur lichte afdelingsbescherming onder je kliniekkleding',
                'hours' => 1,
                'cost_cash' => 500000,
                'cost_stars' => 35,
                'defense_bonus' => 180,
                'backfire_chance' => 30,
                'badge' => '1 Uur • 30% Backfire',
                'icon' => 'fa-solid fa-vest',
            ],
            'tactical_3h' => [
                'id' => 'tactical_3h',
                'name' => 'Zwaar Tactisch Backfire-Vest',
                'subtitle' => '3 uur zwaar stalen/keramisch terugslag-vest — populairste keus voor geld!',
                'hours' => 3,
                'cost_cash' => 1250000,
                'cost_stars' => 65,
                'defense_bonus' => 350,
                'backfire_chance' => 55,
                'badge' => '3 Uur • 55% Backfire',
                'icon' => 'fa-solid fa-shield-halved',
            ],
            'ceramic_6h' => [
                'id' => 'ceramic_6h',
                'name' => 'Syndicaat Keramisch Pantser',
                'hours' => 6,
                'subtitle' => '6 uur militair interventiepantser met hoge ricochet-terugslag',
                'cost_cash' => 2400000,
                'cost_stars' => 110,
                'defense_bonus' => 480,
                'backfire_chance' => 65,
                'badge' => '6 Uur • 65% Backfire',
                'icon' => 'fa-solid fa-user-shield',
            ],
            'titanium_12h' => [
                'id' => 'titanium_12h',
                'name' => 'Directie Titanium Exosuit',
                'subtitle' => '12 uur maximale pantserplaten met dodelijke 75% kogel-terugslag',
                'hours' => 12,
                'cost_cash' => 4500000,
                'cost_stars' => 150,
                'defense_bonus' => 650,
                'backfire_chance' => 75,
                'badge' => '12 Uur • 75% Backfire',
                'icon' => 'fa-solid fa-helmet-safety',
            ],
        ];
    }

    /**
     * Get the active vest configuration if `hasVest()` is true.
     *
     * @return array{id: string, name: string, subtitle: string, hours: int, cost_cash: int, cost_stars: int, defense_bonus: int, backfire_chance: int, badge: string, icon: string}|null
     */
    public function getActiveVestInfo(): ?array
    {
        if (! $this->hasVest()) {
            return null;
        }

        $catalog = self::vestCatalog();
        $tier = ($this->perks ?? [])['vest_tier'] ?? 'tactical_3h';

        return $catalog[$tier] ?? $catalog['tactical_3h'];
    }

    public function getVestDefenseBonus(): int
    {
        $info = $this->getActiveVestInfo();

        return $info ? $info['defense_bonus'] : 0;
    }

    public function getVestBackfireChance(): int
    {
        $info = $this->getActiveVestInfo();

        return $info ? $info['backfire_chance'] : 0;
    }

    /**
     * Catalog of Black Market Ammunition Crates.
     *
     * @return array<string, array{id: string, name: string, subtitle: string, bullets: int, cost_cash: int, cost_stars: int, cold_blooded_bonus: int, icon: string}>
     */
    public static function ammoCatalog(): array
    {
        return [
            'box_50' => [
                'id' => 'box_50',
                'name' => 'Smokkeldoos (+50 Patronen)',
                'subtitle' => 'Compacte munitiedoos voor scherpschutters',
                'bullets' => 50,
                'cost_cash' => 250000,
                'cost_stars' => 50,
                'cold_blooded_bonus' => 0,
                'icon' => 'fa-solid fa-box',
            ],
            'crate_250' => [
                'id' => 'crate_250',
                'name' => 'Paviljoen Munitiekist (+250 Patronen)',
                'subtitle' => 'Inclusief +5% Koudbloedigheid kaliberfocus',
                'bullets' => 250,
                'cost_cash' => 1100000,
                'cost_stars' => 180,
                'cold_blooded_bonus' => 5,
                'icon' => 'fa-solid fa-boxes-stacked',
            ],
            'crate_1000' => [
                'id' => 'crate_1000',
                'name' => 'Syndicaat Wapenkist (+1.000 Patronen)',
                'subtitle' => 'Inclusief +10% Koudbloedigheid kaliberfocus',
                'bullets' => 1000,
                'cost_cash' => 3850000,
                'cost_stars' => 550,
                'cold_blooded_bonus' => 10,
                'icon' => 'fa-solid fa-crates',
            ],
            'container_2500' => [
                'id' => 'container_2500',
                'name' => 'Kartel Arsenaalcontainer (+2.500 Patronen)',
                'subtitle' => 'Zwaar salvo-arsenaal + +15% Koudbloedigheid kaliberfocus',
                'bullets' => 2500,
                'cost_cash' => 8500000,
                'cost_stars' => 1100,
                'cold_blooded_bonus' => 15,
                'icon' => 'fa-solid fa-truck-ramp-box',
            ],
        ];
    }

    /**
     * Catalog of Shadow Intel Operations (15-minute target tracking).
     *
     * @return array<string, array{id: string, name: string, subtitle: string, wait_minutes: int, valid_minutes: int, lockdown_minutes: int, cost_cash: int, cost_stars: int, icon: string}>
     */
    public static function intelCatalog(): array
    {
        return [
            'standard_15m' => [
                'id' => 'standard_15m',
                'name' => 'Camera- & Gangpad Schaduwing',
                'subtitle' => 'Duurt 15 minuten. Onthult 30 min lang de exacte kliniek-vleugel, gedragen pantser en geld op zak.',
                'wait_minutes' => 15,
                'valid_minutes' => 30,
                'lockdown_minutes' => 0,
                'cost_cash' => 350000,
                'cost_stars' => 20,
                'icon' => 'fa-solid fa-video',
            ],
            'express_7m' => [
                'id' => 'express_7m',
                'name' => 'Hoofdbewaker Spoed-Dossier',
                'subtitle' => 'Versnelde opsporing in 7 minuten via omgekochte afdelingshoofden. 45 minuten geldig.',
                'wait_minutes' => 7,
                'valid_minutes' => 45,
                'lockdown_minutes' => 0,
                'cost_cash' => 850000,
                'cost_stars' => 40,
                'icon' => 'fa-solid fa-bolt',
            ],
            'lockdown_15m' => [
                'id' => 'lockdown_15m',
                'name' => 'Celblok-Lockdown & Schaduwing',
                'subtitle' => 'Duurt 15 minuten. Zodra gelokaliseerd wordt de vleugel van het doelwit 10 min vergrendeld tegen vluchten!',
                'wait_minutes' => 15,
                'valid_minutes' => 45,
                'lockdown_minutes' => 10,
                'cost_cash' => 1750000,
                'cost_stars' => 75,
                'icon' => 'fa-solid fa-lock',
            ],
        ];
    }

    /**
     * Get all non-expired shadow intel operations for this character.
     *
     * @return list<array{target_id: int, target_name: string, type: string, type_name: string, started_at: int, ready_at: int, expires_at: int, is_ready: bool, wait_remaining_sec: int, valid_remaining_sec: int, lockdown_minutes: int}>
     */
    public function getActiveIntelOps(): array
    {
        $perks = $this->perks ?? [];
        $ops = $perks['active_intel_ops'] ?? [];
        if (! is_array($ops)) {
            return [];
        }

        $nowTs = now()->timestamp;
        $validOps = [];

        foreach ($ops as $op) {
            if (! is_array($op) || empty($op['target_id']) || empty($op['expires_at'])) {
                continue;
            }
            if ((int) $op['expires_at'] <= $nowTs) {
                continue;
            }

            $readyAt = (int) ($op['ready_at'] ?? $nowTs);
            $expiresAt = (int) $op['expires_at'];
            $isReady = $nowTs >= $readyAt;

            $validOps[] = [
                'target_id' => (int) $op['target_id'],
                'target_name' => (string) ($op['target_name'] ?? 'Onbekend'),
                'type' => (string) ($op['type'] ?? 'standard_15m'),
                'type_name' => (string) ($op['type_name'] ?? 'Camera-Schaduwing'),
                'started_at' => (int) ($op['started_at'] ?? $nowTs),
                'ready_at' => $readyAt,
                'expires_at' => $expiresAt,
                'is_ready' => $isReady,
                'wait_remaining_sec' => $isReady ? 0 : max(0, $readyAt - $nowTs),
                'valid_remaining_sec' => max(0, $expiresAt - $nowTs),
                'lockdown_minutes' => (int) ($op['lockdown_minutes'] ?? 0),
            ];
        }

        return $validOps;
    }

    /**
     * Get active intel operation for a specific target.
     *
     * @return array{target_id: int, target_name: string, type: string, type_name: string, started_at: int, ready_at: int, expires_at: int, is_ready: bool, wait_remaining_sec: int, valid_remaining_sec: int, lockdown_minutes: int}|null
     */
    public function getIntelForTarget(int $targetId): ?array
    {
        foreach ($this->getActiveIntelOps() as $op) {
            if ($op['target_id'] === $targetId) {
                return $op;
            }
        }

        return null;
    }

    /**
     * Check if character's clinic wing is currently locked down by a rival's Celblok-Lockdown operation.
     */
    public function isWingLocked(): bool
    {
        return $this->getWingLockRemainingSeconds() > 0;
    }

    /**
     * Get remaining seconds of wing lockdown.
     */
    public function getWingLockRemainingSeconds(): int
    {
        $perks = $this->perks ?? [];
        $nowTs = now()->timestamp;

        $lockUntil = (int) ($perks['wing_lockdown_until'] ?? 0);
        $lockStart = (int) ($perks['wing_lockdown_start'] ?? 0);

        if ($lockUntil > $nowTs && $nowTs >= $lockStart) {
            return max(0, $lockUntil - $nowTs);
        }

        return 0;
    }

    /**
     * Get current liquidation streak deaths count (0 to 10).
     * At 10 liquidations, the player enters Code Blauw and requires the Forensisch Reanimatie-Serum.
     */
    public function getLiquidationStreakDeaths(): int
    {
        $perks = $this->perks ?? [];

        return max(0, (int) ($perks['liquidation_streak_deaths'] ?? 0));
    }

    /**
     * Increment liquidation streak deaths and trigger Code Blauw Coma on the 10th liquidation.
     */
    public function incrementLiquidationStreakDeaths(): int
    {
        $perks = $this->perks ?? [];
        $count = max(0, (int) ($perks['liquidation_streak_deaths'] ?? 0)) + 1;
        $perks['liquidation_streak_deaths'] = $count;
        $this->perks = $perks;

        if ($count >= self::MAX_LIQUIDATIONS_BEFORE_SERUM) {
            $this->health = 0;
            $this->coma_until = now()->addHours(4);
        }

        $this->save();

        return $count;
    }

    /**
     * Reset liquidation streak deaths back to 0 and revive from Code Blauw.
     */
    public function applyReviveSerum(): void
    {
        $perks = $this->perks ?? [];
        $perks['liquidation_streak_deaths'] = 0;
        $this->perks = $perks;
        $this->coma_until = null;
        $this->ad_coma_progress = 0;
        $this->health = $this->max_health;
        $this->protection_until = now()->addMinutes(30);
        $this->save();
    }

    /**
     * Check if character is at 10/10 liquidations or in Code Blauw Coma requiring the serum.
     */
    public function needsReviveSerum(): bool
    {
        return $this->isComatose() || $this->getLiquidationStreakDeaths() >= self::MAX_LIQUIDATIONS_BEFORE_SERUM;
    }

    /**
     * Maximum item storage units allowed in personal storage based on level and VIP status.
     */
    public function getStorageCapacity(): int
    {
        $lvl = $this->level ?? 1;

        if ($lvl >= 50) {
            $cap = 150;
        } elseif ($lvl >= 30) {
            $cap = 100;
        } elseif ($lvl >= 20) {
            $cap = 75;
        } elseif ($lvl >= 10) {
            $cap = 50;
        } else {
            $cap = 30;
        }

        if ($this->isVip()) {
            $cap += 50;
        }

        if ($this->hasPerk('kluis_uitbreiding')) {
            $cap += 25;
        }

        return $cap;
    }

    /**
     * Count currently owned vehicles in personal garage.
     */
    public function getGarageUsed(): int
    {
        return (int) CharacterVehicle::where('character_id', $this->id)
            ->where('in_gang_garage', false)
            ->count();
    }

    /**
     * Count currently occupied standard storage places (excluding masks and unlimited event varia items).
     */
    public function getStorageUsed(): int
    {
        return (int) CharacterItem::where('character_id', $this->id)
            ->where('quantity', '>', 0)
            ->whereHas('item', function ($q) {
                $q->where('slug', 'not like', 'halloween_%')
                    ->where('slug', '!=', 'bivakmuts-masker')
                    ->where('category', '!=', 'special');
            })
            ->sum('quantity');
    }

    /**
     * Check if character has unlocked the 1-Click Max Energy/HP Auto-Refill module (free for VIP or 50 Stars permanent unlock).
     */
    public function hasAutoRefillPerk(): bool
    {
        return $this->isVip() || $this->hasPerk('auto_refill_module');
    }

    /**
     * Detailed descriptions of what each character stat does in TBS Kliniek.
     *
     * @return array<string, array{label: string, icon: string, short: string, detail: string}>
     */
    public static function statDescriptionsCatalog(): array
    {
        return [
            'strength' => [
                'label' => 'Kracht',
                'icon' => 'fa-solid fa-dumbbell',
                'short' => 'Schade in gevechten, Armpjedrukken & Liquidatie-vuurkracht',
                'detail' => 'Verhoogt jouw aanvalskracht op de Luchtplaats, je kracht in de Armpjedruk Arena en je kogel-impact bij Liquidaties.',
            ],
            'defense' => [
                'label' => 'Verdediging',
                'icon' => 'fa-solid fa-shield-halved',
                'short' => 'Weerstand tegen gevechten, overvallen & kogels',
                'detail' => 'Vermindert schade die je oploopt bij aanvallen op de Luchtplaats en maakt het moeilijker voor vijanden om door je pantser te schieten.',
            ],
            'stamina' => [
                'label' => 'Conditie',
                'icon' => 'fa-solid fa-heart-pulse',
                'short' => 'Uithoudingsvermogen bij fysieke klusjes, diefstal & ontsnappingen',
                'detail' => 'Verhoogt je kans om te slagen bij fysieke diefstallen, cel-ontsnappingen en langdurige gevechten.',
            ],
            'intelligence' => [
                'label' => 'Intelligentie',
                'icon' => 'fa-solid fa-brain',
                'short' => 'Hogere slagingskans bij misdaden, smokkel & kluiskraken',
                'detail' => 'Verhoogt je slagingskans bij ingewikkelde diefstallen, smokkelroutes en kluiskraken, en verlaagt de kans om betrapt te worden.',
            ],
            'charisma' => [
                'label' => 'Charisma',
                'icon' => 'fa-solid fa-comments-dollar',
                'short' => 'Tot 15% korting in de Kliniek Shop + hogere hossel-opbrengst',
                'detail' => 'Geeft 1% korting op alle aankopen in de Kliniek Shop per 10 Charisma (tot 15% korting) en verhoogt je winst bij hosselen en afpersen.',
            ],
            'discipline' => [
                'label' => 'Discipline',
                'icon' => 'fa-solid fa-scale-balanced',
                'short' => 'Betere kans bij behandelaarsgesprekken, verlof & gevechtsfocus',
                'detail' => 'Zorgt voor hogere slagingskansen bij behandelaarsgesprekken, verlof-acties en geeft mentale stabiliteit tijdens gevechten.',
            ],
        ];
    }

    /**
     * Check if a new vehicle can be added to the personal garage.
     */
    public function canAddVehicle(): bool
    {
        return $this->getGarageUsed() < $this->getGarageCapacity();
    }

    /**
     * Check if items can be added to personal storage.
     */
    public function canAddStorageItem(int $amount = 1): bool
    {
        return ($this->getStorageUsed() + $amount) <= $this->getStorageCapacity();
    }

    /**
     * Check if character belongs to a staff member.
     */
    public function isStaff(): bool
    {
        return (bool) ($this->user?->isStaff());
    }
}
