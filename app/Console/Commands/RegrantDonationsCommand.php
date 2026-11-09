<?php

namespace App\Console\Commands;

use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\GameNotification;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('tbs:regrant-donations {--dry-run : Simulate without modifying character data} {--revert-vip-dates : Only restore natural provider renewal dates for VIP subscriptions without re-adding assets}')]
#[Description('Keert uit coulance alle gekochte donatievoordelen (Sterren, Maskers, Kogels, XXL Koffers) opnieuw uit aan donateurs voor Seizoen 2')]
class RegrantDonationsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('revert-vip-dates')) {
            $this->info('Herstellen van natuurlijke VIP-vervaldata via externe provider...');
            $updates = [
                66 => '2026-12-29 22:56:36',
                70 => '2026-12-27 23:28:52',
                113 => '2026-12-10 21:52:11',
            ];
            foreach ($updates as $charId => $date) {
                $c = Character::find($charId);
                if ($c) {
                    $c->vip_expires_at = Carbon::parse($date);
                    $c->is_vip = true;
                    $c->save();
                    $this->info("   -> {$c->name} (ID {$c->id}) VIP hersteld naar provider datum: {$date}");
                }
            }
            $this->info('VIP vervaldatums succesvol gesynchroniseerd met provider renewal.');

            return self::SUCCESS;
        }

        $isDryRun = (bool) $this->option('dry-run');

        $this->info('====================================================');
        $this->info('TBS KLINIEK - COULANCE HER-UITKERING DONATIES');
        $this->info('====================================================');

        if ($isDryRun) {
            $this->warn('DRY RUN: Er worden geen wijzigingen opgeslagen.');
        }

        $orders = BuyMeACoffeeOrder::with('character')
            ->whereNotNull('character_id')
            ->where('status', '!=', 'cancelled')
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->warn('Geen gekoppelde donatie-orders gevonden.');

            return self::SUCCESS;
        }

        // Group by character
        $grouped = $orders->groupBy('character_id');
        $summary = [];

        DB::beginTransaction();

        try {
            foreach ($grouped as $characterId => $charOrders) {
                /** @var Character|null $character */
                $character = Character::find($characterId);
                if (! $character) {
                    $this->warn("Karakter ID {$characterId} niet gevonden, sla over.");
                    continue;
                }

                $totalStars = 0;
                $totalMasks = 0;
                $totalBullets = 0;
                $totalCases = 0;
                $totalVipDays = 0;
                $isLifetime = false;
                $totalAmount = 0.0;
                $orderCount = $charOrders->count();

                foreach ($charOrders as $order) {
                    $totalAmount += (float) $order->amount;
                    $tier = (string) ($order->membership_tier ?? '');
                    $tierLower = strtolower($tier);
                    $amt = (float) $order->amount;
                    $isMembership = (bool) $order->is_membership;

                    // A. Single Coffee / Donation
                    if (! $isMembership || str_contains($tierLower, 'koffie') || str_contains($tierLower, 'bakkie')) {
                        if (str_contains($tierLower, '5x') || $amt >= 15.0) {
                            $totalStars += 160;
                            $totalMasks += 6;
                            $totalCases += 2;
                            $totalBullets += 500;
                        } elseif (str_contains($tierLower, '3x') || $amt >= 9.0) {
                            $totalStars += 85;
                            $totalMasks += 3;
                            $totalCases += 1;
                            $totalBullets += 200;
                        } else {
                            $totalStars += max(30, (int) round($amt * 10));
                            $totalMasks += 1;
                            $totalBullets += 50;
                        }
                        continue;
                    }

                    // B. Memberships
                    if (str_contains($tierLower, 'lifetime')) {
                        $isLifetime = true;
                        $totalStars += 1500;
                        $totalMasks += 30;
                        $totalCases += 10;
                        $totalBullets += 1000;
                    } elseif (str_contains($tierLower, 'baas')) {
                        $totalStars += 600;
                        $totalMasks += 12;
                        $totalCases += 3;
                        $totalBullets += 500;
                        $totalVipDays += 31;
                    } elseif (str_contains($tierLower, 'elite') || str_contains($tierLower, 'gangster')) {
                        $totalStars += 250;
                        $totalMasks += 5;
                        $totalCases += 1;
                        $totalBullets += 250;
                        $totalVipDays += 31;
                    } else {
                        // TBS V.I.P. Lid
                        $totalStars += 100;
                        $totalMasks += 2;
                        $totalBullets += 100;
                        $totalVipDays += 31;
                    }
                }

                // Apply rewards
                $oldStars = (int) $character->stars;
                $oldMasks = (int) $character->masks;
                $oldBullets = (int) $character->bullets;
                $oldCases = (int) $character->xxl_cases;

                $newStars = $oldStars + $totalStars;
                $newMasks = $oldMasks + $totalMasks;
                $newBullets = $oldBullets + $totalBullets;
                $newCases = $oldCases + $totalCases;

                if (! $isDryRun) {
                    $character->stars = $newStars;
                    $character->masks = $newMasks;
                    $character->bullets = $newBullets;
                    $character->xxl_cases = $newCases;

                    // VIP Status blijft behouden via externe provider (maandelijkse incasso loopt door)
                    // We verlengen vip_expires_at niet handmatig
                    if ($character->id === 66) {
                        $character->vip_expires_at = Carbon::parse('2026-12-29 22:56:36');
                    } elseif ($character->id === 70) {
                        $character->vip_expires_at = Carbon::parse('2026-12-27 23:28:52');
                    } elseif ($character->id === 113) {
                        $character->vip_expires_at = Carbon::parse('2026-12-10 21:52:11');
                    } elseif ($isLifetime) {
                        $character->vip_expires_at = Carbon::now()->addYears(99);
                        $perks = $character->perks ?? [];
                        $perks['supporter_tier'] = 4;
                        $perks['has_custom_name_color'] = true;
                        $perks['has_sparkle_effect'] = true;
                        $perks['hourly_cash_bonus'] = 50000;
                        $perks['is_donator'] = true;
                        $character->perks = $perks;
                    }

                    $character->save();

                    // Notification
                    try {
                        GameNotification::create([
                            'character_id' => $character->id,
                            'type' => 'bmac_coulance',
                            'icon' => 'fa-gift',
                            'title' => '🌟 Seizoen 2 Sprint: Coulance Donatievoordelen Uitgekeerd!',
                            'message' => "Uit coulance voor de Seizoensreset zijn al jouw gekochte donatievoordelen opnieuw aan je account toegevoegd! Je hebt ontvangen: +{$totalStars} Sterren ⭐, +{$totalMasks} Maskers 🎭, +{$totalBullets} Kogels 🔫" . ($totalCases > 0 ? " en +{$totalCases} XXL Koffers 🧰" : "") . ". Heel veel succes en strijdlust in Seizoen 2!",
                            'is_read' => false,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning("Notification failed for character {$character->id}: " . $e->getMessage());
                    }

                    // Audit Log
                    try {
                        AuditLogService::log(
                            action: 'buymeacoffee_coulance_regrant',
                            category: 'vip',
                            description: "Coulance her-uitkering donaties voor Seizoen 2: +{$totalStars} Sterren, +{$totalMasks} Maskers, +{$totalBullets} Kogels, +{$totalCases} XXL Koffers toegekend aan {$character->name}",
                            character: $character,
                            user: $character->user,
                            amount: (int) $totalAmount,
                            details: [
                                'order_count' => $orderCount,
                                'stars_added' => $totalStars,
                                'masks_added' => $totalMasks,
                                'bullets_added' => $totalBullets,
                                'cases_added' => $totalCases,
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::warning("AuditLog failed for character {$character->id}: " . $e->getMessage());
                    }
                }

                $summary[] = [
                    'id' => $character->id,
                    'name' => $character->name,
                    'orders' => $orderCount,
                    'donated' => '€' . number_format($totalAmount, 2, ',', '.'),
                    'stars' => "+{$totalStars} ({$oldStars} -> {$newStars})",
                    'masks' => "+{$totalMasks} ({$oldMasks} -> {$newMasks})",
                    'bullets' => "+{$totalBullets} ({$oldBullets} -> {$newBullets})",
                    'cases' => "+{$totalCases} ({$oldCases} -> {$newCases})",
                    'vip' => $isLifetime ? 'VIP LIFETIME (99j)' : ($totalVipDays > 0 ? "+{$totalVipDays} dagen" : 'Behouden'),
                ];
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->warn('DRY RUN: Wijzigingen zijn teruggedraaid.');
            } else {
                DB::commit();
                $this->info('====================================================');
                $this->info('COULANCE HER-UITKERING MET SUCCES VOLTOOID!');
                $this->info('====================================================');
            }

            $this->table(
                ['ID', 'Karakter', 'Orders', 'Totaal Donaties', 'Sterren ⭐', 'Maskers 🎭', 'Kogels 🔫', 'XXL Koffers 🧰', 'VIP Status'],
                $summary
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Fout tijdens coulance her-uitkering: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
