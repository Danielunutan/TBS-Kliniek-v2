<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\Gang;
use App\Support\GangPropertyCatalog;

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
     * Property hourly base income catalog.
     * Each commercial or industrial property owned by the gang adds substantial recurring hourly wage.
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
        // 2. Productie, Labs & Fabrieken
        'wietkwekerij' => 4000,
        'valsgeld_drukkerij' => 7000,
        'sigaretten_fabriek' => 10000,
        'chemisch_lab' => 15000,
        'crypto_mining_bunker' => 20000,
        'wapenfabriek' => 30000,
        'synthese_complex' => 40000,
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
     * Calculate hourly wage for a given gang including properties, NPCs, and luxury bonuses.
     */
    public function calculateHourlyWage(?Gang $gang): int
    {
        if (! $gang) {
            return 0;
        }

        // 1. Base starting wage and Smokkelkiosken
        $base = self::BASE_HOURLY_WAGE + (($gang->bling_shops ?? 0) * self::SHOP_BONUS_WAGE);

        // 2. Gang Properties (Panden)
        $properties = $gang->relationLoaded('properties')
            ? $gang->properties
            : $gang->properties()->get();

        $propsIncome = 0;
        $luxuryBonusPct = 0;
        $catalog = GangPropertyCatalog::all();

        foreach ($properties as $prop) {
            $key = $prop->property_key;
            $propsIncome += (self::PROPERTY_HOURLY_BASE[$key] ?? 1000);

            $def = $catalog[$key] ?? null;
            if ($def && isset($def['wage_bonus_pct'])) {
                $luxuryBonusPct += (int) $def['wage_bonus_pct'];
            }
        }

        // 3. Hired Gang NPCs
        $npcs = $gang->relationLoaded('npcs')
            ? $gang->npcs
            : $gang->npcs()->get();

        $npcsIncome = 0;
        $npcWageBonusPct = 0;
        $npcCatalog = GangPropertyCatalog::npcs();

        foreach ($npcs as $npc) {
            $npcsIncome += 500; // Base contribution per specialist
            $nDef = $npcCatalog[$npc->npc_key] ?? null;
            if ($nDef && ($nDef['bonus_type'] ?? '') === 'wage_bonus') {
                $npcWageBonusPct += (int) ($nDef['bonus_value'] ?? 0);
            }
        }

        // 4. Gang Respect bonus (underworld dominance)
        $respectBonus = min(50000, (int) round(($gang->respect ?? 0) / 40));

        $subtotal = $base + $propsIncome + $npcsIncome + $respectBonus;

        // Apply luxury and NPC percentage bonuses
        $totalBonusPct = $luxuryBonusPct + $npcWageBonusPct;
        $multiplier = 1 + ($totalBonusPct / 100);

        return (int) round($subtotal * $multiplier);
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

        $hourlyWage = $this->calculateHourlyWage($character->gang);
        $dailyWage = $hourlyWage * 24;

        $now = now();
        $lastPayout = $character->last_gang_wage_at ?? $character->updated_at ?? $now;
        $secondsElapsed = ($lastPayout > $now) ? 0 : (int) $lastPayout->diffInSeconds($now);

        $pendingHours = (int) floor($secondsElapsed / self::WAGE_INTERVAL_SECONDS);
        $pendingAmount = $pendingHours * $hourlyWage;

        $secondsUntilNext = self::WAGE_INTERVAL_SECONDS - ($secondsElapsed % self::WAGE_INTERVAL_SECONDS);
        $minutesLeft = (int) floor($secondsUntilNext / 60);
        $secondsLeft = $secondsUntilNext % 60;
        $nextHuman = "{$minutesLeft}m {$secondsLeft}s";

        return [
            'in_gang' => true,
            'hourly_wage' => $hourlyWage,
            'daily_wage' => $dailyWage,
            'pending_hours' => $pendingHours,
            'pending_amount' => $pendingAmount,
            'seconds_until_next' => $secondsUntilNext,
            'next_payout_human' => $nextHuman,
            'last_payout_at' => $character->last_gang_wage_at?->format('H:i d-m-Y'),
        ];
    }

    /**
     * Check and automatically pay out all accrued hourly wages to the character's bank account.
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

        // Initialize if never set
        if (! $character->last_gang_wage_at) {
            $character->last_gang_wage_at = $now;
            $character->save();

            return null;
        }

        if ($character->last_gang_wage_at > $now) {
            return null;
        }

        $secondsElapsed = (int) $character->last_gang_wage_at->diffInSeconds($now);

        if ($secondsElapsed < self::WAGE_INTERVAL_SECONDS) {
            return null;
        }

        $hours = (int) floor($secondsElapsed / self::WAGE_INTERVAL_SECONDS);

        $hourlyWage = $this->calculateHourlyWage($gang);
        $totalPayout = $hours * $hourlyWage;

        if ($totalPayout <= 0) {
            $character->last_gang_wage_at = $now;
            $character->save();

            return null;
        }

        // Contant geld uitbetaling direct op zak van het personage!
        $character->cash += $totalPayout;
        // Reset last wage payout timestamp directly to now so timer cleanly begins for the next hour
        $character->last_gang_wage_at = $now;
        $character->save();

        if ($sendNotificationMessage) {
            GameMessage::create([
                'recipient_id' => $character->id,
                'sender_id' => $character->id,
                'sender_name' => 'Bende Salaris Administratie',
                'subject' => '💰 Bende Uurloon Uitbetaald: €'.number_format($totalPayout, 0, ',', '.'),
                'content' => "Gefeliciteerd!\n\nJe hebt zojuist voor {$hours} gewerkte uren bij bende '{$gang->name}' in totaal €".number_format($totalPayout, 0, ',', '.').' contant geld ontvangen op zak (Uurloon: €'.number_format($hourlyWage, 0, ',', '.').').',
                'type' => 'gang',
            ]);
        }

        return [
            'hours' => $hours,
            'amount' => $totalPayout,
            'hourly_wage' => $hourlyWage,
            'gang_name' => $gang->name,
        ];
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
