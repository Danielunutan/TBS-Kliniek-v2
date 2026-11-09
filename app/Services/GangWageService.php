<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Support\GangPropertyCatalog;
use Illuminate\Support\Facades\DB;

class GangWageService
{
    /**
     * Seconds per 1 wage hour cycle (3600 seconds = 1 hour).
     */
    public const WAGE_INTERVAL_SECONDS = 3600;

    /**
     * Base hourly wage for any gang member.
     */
    public const BASE_HOURLY_WAGE = 150;

    /**
     * Extra hourly wage added per Bling Bling Shop owned by the gang.
     */
    public const SHOP_BONUS_WAGE = 100;

    /**
     * Maximum number of smokkelkiosken that contribute to the hourly wage.
     * Prevents unlimited runaway inflation while keeping kiosk investments rewarding.
     */
    public const MAX_CONTRIBUTING_KIOSKS = 250;

    /**
     * Property hourly base income catalog.
     * Each commercial, industrial, or luxury property owned by the gang adds recurring hourly wage.
     */
    public const PROPERTY_HOURLY_BASE = [
        // 1. Opslag & Garages
        'kleine_loods' => 1000,
        'garagebox_complex' => 2000,
        'haven_loods' => 3000,
        'industrieele_loods' => 5000,
        'chop_shop_garage' => 7500,
        'trucking_terminal' => 10000,
        'vliegveld_hangar' => 15000,
        'ondergrondse_bunker' => 20000,
        // 2. Productie, Labs, Administratie & Vluchtplan
        'wietkwekerij' => 4000,
        'valsgeld_drukkerij' => 7000,
        'sigaretten_fabriek' => 10000,
        'chemisch_lab' => 15000,
        'administratie_kantoor' => 18000,
        'crypto_mining_bunker' => 20000,
        'vluchtplan_complex' => 25000,
        'wapenfabriek' => 30000,
        'synthese_complex' => 40000,
        'smokkelhaven_terminal' => 35000,
        // 3. Luxe Panden & Hoofdkwartieren
        'stripclub_lounge' => 10000,
        'casino_penthouse' => 15000,
        'luxe_penthouse' => 20000,
        'haven_jachthuis' => 25000,
        'monumentaal_grachtenpand' => 35000,
        'gang_villa' => 50000,
        'fort_eiland' => 75000,
    ];

    /**
     * Calculate hourly wage for a given gang including properties, vehicle fleet, NPCs,
     * respect dominance, and character-specific level and inzet scaling.
     */
    public function calculateHourlyWage(?Gang $gang, ?Character $character = null): int
    {
        if (! $gang) {
            return 0;
        }

        // 1. Base starting wage and capped Smokkelkiosken
        $effectiveKiosks = min(self::MAX_CONTRIBUTING_KIOSKS, max(0, (int) ($gang->bling_shops ?? 0)));
        $kiosksIncome = $effectiveKiosks * self::SHOP_BONUS_WAGE;
        $base = self::BASE_HOURLY_WAGE + $kiosksIncome;

        // 2. Gang Properties (Panden)
        $properties = $gang->relationLoaded('properties')
            ? $gang->properties
            : $gang->properties()->get();

        $propsIncome = 0;
        $luxuryBonusPct = 0;
        $catalog = GangPropertyCatalog::all();

        foreach ($properties as $prop) {
            $key = $prop->property_key;
            $propsIncome += (self::PROPERTY_HOURLY_BASE[$key] ?? 2500);

            $def = $catalog[$key] ?? null;
            if ($def && isset($def['wage_bonus_pct'])) {
                $luxuryBonusPct += (int) $def['wage_bonus_pct'];
            }
        }

        // 3. Bende Wagenpark (Stored fleet in gang garage generates logistics / hustle revenue)
        $storedVehicles = $gang->relationLoaded('storedVehicles')
            ? $gang->storedVehicles
            : $gang->storedVehicles()->with('vehicle')->get();

        $vehiclesIncome = 0;
        foreach ($storedVehicles as $sv) {
            $vType = $sv->vehicle?->type ?? 'auto';
            $vBase = match ($vType) {
                'motor' => 1500,
                'auto' => 4000,
                'boot' => 12500,
                'helikopter', 'vliegtuig' => 25000,
                default => 3000,
            };
            $condPct = max(25, min(100, (int) ($sv->current_condition ?? 100))) / 100;
            $vehiclesIncome += (int) round($vBase * $condPct);
        }

        // 4. Hired Gang NPCs
        $npcs = $gang->relationLoaded('npcs')
            ? $gang->npcs
            : $gang->npcs()->get();

        $npcsIncome = 0;
        $npcWageBonusPct = 0;
        $npcCatalog = GangPropertyCatalog::npcs();

        foreach ($npcs as $npc) {
            if ($npc->isExpired()) {
                continue;
            }
            $npcsIncome += 500; // Base contribution per specialist
            $nDef = $npcCatalog[$npc->npc_key] ?? null;
            if ($nDef && ($nDef['bonus_type'] ?? '') === 'wage_bonus') {
                $npcWageBonusPct += (int) ($nDef['bonus_value'] ?? 0);
            }
        }

        // 5. Gang Respect bonus (+1% per 100.000 respect, capped at +50%)
        $gangRespect = (int) ($gang->total_respect ?? $gang->respect ?? 0);
        $respectBonusPct = min(50, (int) floor($gangRespect / 100000));

        // 6. Gang War Dominance (Kliniek Heerschappij: +25% bonus na gewonnen bendeoorlog)
        $warDominancePct = 0;
        if (method_exists($gang, 'hasWarDominance') && $gang->hasWarDominance()) {
            $warDominancePct = 25;
        }

        $subtotal = $base + $propsIncome + $vehiclesIncome + $npcsIncome;

        // Apply luxury, respect, NPC and war percentage multipliers
        $totalBonusPct = $luxuryBonusPct + $respectBonusPct + $npcWageBonusPct + $warDominancePct;
        $multiplier = 1.0 + ($totalBonusPct / 100);
        $baseHourlyWage = (int) round($subtotal * $multiplier);

        if (! $character) {
            return $baseHourlyWage;
        }

        // 7. Character Level Scaling (+2.5% per character level for high-tier member value)
        $charLevel = max(1, (int) ($character->level ?? 1));
        $levelMultiplier = 1.0 + (($charLevel - 1) * 0.025);

        // 8. Role Scaling (Leadership & Responsibilities)
        $roleMultiplier = match ($character->gang_role ?? 'member') {
            'leader' => 1.25,
            'co_leader' => 1.15,
            'officer' => 1.10,
            default => 1.00,
        };

        // 9. Inzet & Activity Factor (active players maintain 100% full wage)
        $activityMultiplier = 1.0;
        if ($character->last_active_at) {
            $hoursInactive = (int) $character->last_active_at->diffInHours(now());
            if ($hoursInactive > 168) {
                $activityMultiplier = 0.35;
            } elseif ($hoursInactive > 72) {
                $activityMultiplier = 0.50;
            } elseif ($hoursInactive > 24) {
                $activityMultiplier = 0.75;
            }
        }

        return max(50, (int) round($baseHourlyWage * $levelMultiplier * $roleMultiplier * $activityMultiplier));
    }

    /**
     * Get a comprehensive breakdown of wage components for UI display.
     *
     * @return array<string, mixed>
     */
    public function getWageBreakdown(?Gang $gang, ?Character $character = null): array
    {
        if (! $gang) {
            return [];
        }

        $effectiveKiosks = min(self::MAX_CONTRIBUTING_KIOSKS, max(0, (int) ($gang->bling_shops ?? 0)));
        $kiosksIncome = $effectiveKiosks * self::SHOP_BONUS_WAGE;

        $properties = $gang->relationLoaded('properties') ? $gang->properties : $gang->properties()->get();
        $propsIncome = 0;
        $luxuryBonusPct = 0;
        $catalog = GangPropertyCatalog::all();
        foreach ($properties as $prop) {
            $key = $prop->property_key;
            $propsIncome += (self::PROPERTY_HOURLY_BASE[$key] ?? 2500);
            $def = $catalog[$key] ?? null;
            if ($def && isset($def['wage_bonus_pct'])) {
                $luxuryBonusPct += (int) $def['wage_bonus_pct'];
            }
        }

        $storedVehicles = $gang->relationLoaded('storedVehicles') ? $gang->storedVehicles : $gang->storedVehicles()->with('vehicle')->get();
        $vehiclesIncome = 0;
        foreach ($storedVehicles as $sv) {
            $vType = $sv->vehicle?->type ?? 'auto';
            $vBase = match ($vType) {
                'motor' => 1500,
                'auto' => 4000,
                'boot' => 12500,
                'helikopter', 'vliegtuig' => 25000,
                default => 3000,
            };
            $condPct = max(25, min(100, (int) ($sv->current_condition ?? 100))) / 100;
            $vehiclesIncome += (int) round($vBase * $condPct);
        }

        $gangRespect = (int) ($gang->total_respect ?? $gang->respect ?? 0);
        $respectBonusPct = min(50, (int) floor($gangRespect / 100000));
        $warDominancePct = method_exists($gang, 'hasWarDominance') && $gang->hasWarDominance() ? 25 : 0;
        $totalBonusPct = $luxuryBonusPct + $respectBonusPct + $warDominancePct;

        $collectiveWage = $this->calculateHourlyWage($gang);
        $characterWage = $character ? $this->calculateHourlyWage($gang, $character) : $collectiveWage;

        return [
            'kiosks_count' => (int) ($gang->bling_shops ?? 0),
            'kiosks_capped' => $effectiveKiosks,
            'kiosks_income' => $kiosksIncome,
            'properties_count' => $properties->count(),
            'properties_income' => $propsIncome,
            'vehicles_count' => $storedVehicles->count(),
            'vehicles_income' => $vehiclesIncome,
            'respect_bonus_pct' => $respectBonusPct,
            'luxury_bonus_pct' => $luxuryBonusPct,
            'war_dominance_pct' => $warDominancePct,
            'total_bonus_pct' => $totalBonusPct,
            'collective_hourly_wage' => $collectiveWage,
            'character_hourly_wage' => $characterWage,
            'level_multiplier' => $character ? 1.0 + ((max(1, (int) $character->level) - 1) * 0.02) : 1.0,
            'role_multiplier' => match ($character?->gang_role ?? 'member') {
                'leader' => 1.25,
                'co_leader' => 1.15,
                'officer' => 1.10,
                default => 1.00,
            },
        ];
    }

    /**
     * Calculate 24-hour daily wage for a gang.
     */
    public function calculateDailyWage(?Gang $gang): int
    {
        return $this->calculateHourlyWage($gang) * 24;
    }

    /**
     * Get detailed payout status and countdown for a character.
     * All countdowns and payouts are globally synchronized to the top of the hour (:00:00).
     *
     * @return array{in_gang: bool, hourly_wage: int, daily_wage: int, pending_hours: int, pending_amount: int, seconds_until_next: int, next_payout_human: string, last_payout_at: ?string}
     */
    public function getWageDetails(Character $character): array
    {
        if (! $character->gang_id || ! $character->gang) {
            return [
                'in_gang' => false,
                'hourly_wage' => 0,
                'daily_wage' => 0,
                'pending_hours' => 0,
                'pending_amount' => 0,
                'seconds_until_next' => self::WAGE_INTERVAL_SECONDS,
                'next_payout_human' => '0m 0s',
                'last_payout_at' => null,
            ];
        }

        $baseWage = $this->calculateHourlyWage($character->gang);
        $hourlyWage = $this->calculateHourlyWage($character->gang, $character);
        $dailyWage = $hourlyWage * 24;

        $charLevel = max(1, (int) ($character->level ?? 1));
        $levelBonusPct = (int) round(($charLevel - 1) * 2.5);

        $roleBonusPct = match ($character->gang_role ?? 'member') {
            'leader' => 25,
            'co_leader' => 15,
            'officer' => 10,
            default => 0,
        };

        $now = now();

        // 1. Globally synchronized countdown to the top of the next hour (:00:00)
        $nextHour = $now->copy()->addHour()->startOfHour();
        $secondsUntilNext = max(0, (int) $now->diffInSeconds($nextHour));
        $minutesLeft = (int) floor($secondsUntilNext / 60);
        $secondsLeft = $secondsUntilNext % 60;
        $nextHuman = "{$minutesLeft}m {$secondsLeft}s";

        // 2. Pending uncollected hours
        $lastPayout = $character->last_gang_wage_at
            ? $character->last_gang_wage_at->copy()->startOfHour()
            : $now->copy()->startOfHour();

        $secondsElapsed = ($lastPayout >= $now) ? 0 : (int) $lastPayout->diffInSeconds($now);
        $pendingHours = min(168, (int) floor($secondsElapsed / self::WAGE_INTERVAL_SECONDS));
        $pendingAmount = $pendingHours * $hourlyWage;

        return [
            'in_gang' => true,
            'base_hourly_wage' => $baseWage,
            'hourly_wage' => $hourlyWage,
            'daily_wage' => $dailyWage,
            'level_bonus_pct' => $levelBonusPct,
            'role_bonus_pct' => $roleBonusPct,
            'pending_hours' => $pendingHours,
            'pending_amount' => $pendingAmount,
            'seconds_until_next' => $secondsUntilNext,
            'next_payout_human' => $nextHuman,
            'last_payout_at' => $character->last_gang_wage_at?->format('H:i d-m-Y'),
        ];
    }

    /**
     * Check and automatically pay out all accrued hourly wages to the character's pocket (cash).
     * Anchors timestamps cleanly to the top of the hour so timers stay synchronized across all players.
     *
     * @return array{hours: int, amount: int, hourly_wage: int, gang_name: string}|null
     */
    public function payoutPendingWages(Character $character, bool $sendNotificationMessage = false): ?array
    {
        if (! $character->gang_id) {
            return null;
        }

        $gang = $character->gang ?? Gang::find($character->gang_id);
        if (! $gang) {
            return null;
        }

        $now = now();

        return DB::transaction(function () use ($character, $gang, $now, $sendNotificationMessage): ?array {
            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter || ! $lockedCharacter->gang_id) {
                return null;
            }

            // Initialize if never set: anchor to the current hour boundary
            if (! $lockedCharacter->last_gang_wage_at) {
                $lockedCharacter->last_gang_wage_at = $now->copy()->startOfHour();
                $lockedCharacter->save();
                $character->last_gang_wage_at = $lockedCharacter->last_gang_wage_at;

                return null;
            }

            if ($lockedCharacter->last_gang_wage_at > $now) {
                return null;
            }

            // Always calculate from the start of the hour anchor so all members stay globally synchronized to :00:00
            $lastWageAnchor = $lockedCharacter->last_gang_wage_at->copy()->startOfHour();
            $secondsElapsed = (int) $lastWageAnchor->diffInSeconds($now);

            if ($secondsElapsed < self::WAGE_INTERVAL_SECONDS) {
                return null;
            }

            $rawHours = (int) floor($secondsElapsed / self::WAGE_INTERVAL_SECONDS);
            if ($rawHours <= 0) {
                return null;
            }

            $hours = min(168, $rawHours);
            $hourlyWage = $this->calculateHourlyWage($gang, $lockedCharacter);
            $totalPayout = $hours * $hourlyWage;

            // Advance last_gang_wage_at by the elapsed hours, snapped to the start of the hour
            $lockedCharacter->last_gang_wage_at = $lastWageAnchor->copy()->addHours($rawHours)->startOfHour();

            if ($totalPayout > 0) {
                // Contant geld uitbetaling direct op zak van het personage
                $lockedCharacter->cash += $totalPayout;
                EventCashHuntService::record($lockedCharacter, $totalPayout, 'gang_wage');
            }

            $lockedCharacter->save();
            $character->cash = $lockedCharacter->cash;
            $character->last_gang_wage_at = $lockedCharacter->last_gang_wage_at;

            if ($totalPayout > 0) {
                // In-game bell notification (instantly visible on every page in header)
                GameNotification::create([
                    'character_id' => $lockedCharacter->id,
                    'type' => 'gang_wage',
                    'icon' => 'fa-coins',
                    'title' => '💰 Bende Uurloon Uitbetaald!',
                    'message' => "Je hebt zojuist voor {$hours} gewerkte uur(en) bij bende '{$gang->name}' in totaal €".number_format($totalPayout, 0, ',', '.').' contant geld op zak ontvangen!',
                    'is_read' => false,
                ]);

                if ($sendNotificationMessage) {
                    GameMessage::create([
                        'recipient_id' => $lockedCharacter->id,
                        'sender_id' => $lockedCharacter->id,
                        'sender_name' => 'Bende Salaris Administratie',
                        'subject' => '💰 Bende Uurloon Uitbetaald: €'.number_format($totalPayout, 0, ',', '.'),
                        'content' => "Gefeliciteerd!\n\nJe hebt zojuist voor {$hours} gewerkte uren bij bende '{$gang->name}' in totaal €".number_format($totalPayout, 0, ',', '.').' contant geld ontvangen op zak (Uurloon: €'.number_format($hourlyWage, 0, ',', '.').').',
                        'type' => 'gang',
                    ]);
                }
            }

            return [
                'hours' => $hours,
                'amount' => $totalPayout,
                'hourly_wage' => $hourlyWage,
                'gang_name' => $gang->name,
            ];
        });
    }

    /**
     * Batch pay all active gang members across the game (for cron / console command).
     */
    public function payAllMembers(): int
    {
        $characters = Character::whereNotNull('gang_id')->with('gang')->get();
        $totalPaidOut = 0;

        foreach ($characters as $character) {
            $payout = $this->payoutPendingWages($character, true);
            if ($payout) {
                $totalPaidOut += $payout['amount'];
            }
        }

        return $totalPaidOut;
    }
}
