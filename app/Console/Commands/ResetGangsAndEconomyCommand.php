<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\Gang;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('tbs:reset-gangs-and-economy {--force : Voer direct uit zonder bevestiging}')]
#[Description('Reset alle bendes volledig (kas, respect, panden, npcs) met behoud van gekochte bende chat, en zet Dennis, Bossie en alle spelers op schone start')]
class ResetGangsAndEconomyCommand extends Command
{
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('TBS KLINIEK - BENDE EN ECONOMIE RESET PROCEDURE');
        $this->info('====================================================');

        DB::beginTransaction();

        try {
            // 1. Reset Alle Bendes (behalve has_gang_chat)
            $this->info('1. Bendes resetten (kas, respect, maskers, attack power, protection)...');
            if (Schema::hasTable('gangs')) {
                $gangUpdate = [
                    'treasury' => 0,
                    'protected_treasury' => 0,
                    'respect' => 0,
                    'masks' => 0,
                    'gang_stars' => 0,
                    'bling_shops' => 0,
                    'protection_points' => 1000,
                    'attack_power' => 500,
                    'leave_fee_multiplier' => 0,
                    'min_respect' => 0,
                    'shield_until' => null,
                    'vip_boost_until' => null,
                    'war_dominance_until' => null,
                    'last_attack_at' => null,
                    'personal_shopper' => null,
                    'money_magnet' => null,
                ];

                $gangColumns = Schema::getColumnListing('gangs');
                $filteredGangUpdates = array_intersect_key($gangUpdate, array_flip($gangColumns));
                Gang::query()->update($filteredGangUpdates);

                $gangCount = Gang::count();
                $this->info("   -> {$gangCount} bendes gereset naar 0 kas/respect. Gekochte bende-chat blijft 100% behouden!");
            }

            // 2. Leegmaken van bende-inventaris en bezittingen
            $gangTables = [
                'gang_donations',
                'gang_properties',
                'gang_npcs',
                'gang_attacks',
                'gang_wars',
                'gang_items',
                'gang_requests',
                'heist_logs',
                'control_point_logs',
            ];

            foreach ($gangTables as $tbl) {
                if (Schema::hasTable($tbl)) {
                    DB::table($tbl)->truncate();
                    $this->line("   -> Tabel [{$tbl}] geleegd.");
                }
            }

            // Bende-voertuigen uit de garage halen of resetten
            if (Schema::hasTable('character_vehicles')) {
                $vehCols = Schema::getColumnListing('character_vehicles');
                if (in_array('is_gang_owned', $vehCols, true)) {
                    DB::table('character_vehicles')->where('is_gang_owned', true)->delete();
                }
                if (in_array('in_gang_garage', $vehCols, true)) {
                    DB::table('character_vehicles')->where('in_gang_garage', true)->update([
                        'in_gang_garage' => false,
                        'gang_id' => null,
                        'stored_at' => null,
                        'stored_by_character_id' => null,
                    ]);
                }
                $this->line('   -> Bende garage voertuigen gereset.');
            }

            // 3. Controleposten resetten naar neutraal
            if (Schema::hasTable('control_points')) {
                ControlPoint::query()->update([
                    'current_character_id' => null,
                    'current_gang_id' => null,
                    'gang_id' => null,
                    'control_level' => 0,
                    'captured_at' => null,
                    'last_contested_at' => null,
                    'total_captures' => 0,
                ]);
                $this->info('3. Controleposten gereset naar neutraal.');
            }

            // 4. Karakters herstellen naar schone startwaarden
            $this->info('4. Karakters resetten naar basis Level 1, €250 cash, €1.000 bank, 10 respect...');
            $now = now();

            // Dennis (#86) en Bossie (#66) specifiek
            Character::where('id', 86)->update([
                'level' => 1,
                'xp' => 0,
                'total_xp' => 0,
                'respect' => 10,
                'today_respect' => 0,
                'cash' => 250,
                'bank' => 1000,
                'energy' => 100,
                'health' => 100,
                'last_gang_wage_at' => $now,
            ]);
            $this->info('   -> Dennis (#86) gereset naar Level 1, 10 Respect, €250 Cash, €1.000 Bank.');

            Character::where('id', 66)->update([
                'level' => 1,
                'xp' => 0,
                'total_xp' => 0,
                'respect' => 10,
                'today_respect' => 0,
                'cash' => 250,
                'bank' => 1000,
                'energy' => 100,
                'health' => 100,
                'last_gang_wage_at' => $now,
            ]);
            $this->info('   -> Bossie (#66) gereset naar Level 1, 10 Respect, €250 Cash, €1.000 Bank.');

            // Alle karakters die meer dan startgeld of meer dan 10 respect / level 1 hebben
            Character::query()
                ->where('cash', '>', 250)
                ->orWhere('bank', '>', 1000)
                ->orWhere('respect', '>', 10)
                ->orWhere('level', '>', 1)
                ->update([
                    'level' => 1,
                    'xp' => 0,
                    'total_xp' => 0,
                    'cash' => 250,
                    'bank' => 1000,
                    'respect' => 10,
                    'today_respect' => 0,
                    'last_gang_wage_at' => $now,
                    'daily_attacks_count' => 0,
                    'fights_won' => 0,
                    'fights_lost' => 0,
                    'thefts_succeeded' => 0,
                    'thefts_failed' => 0,
                ]);

            // Sync alle karakters hun last_gang_wage_at naar nu zodat er geen spookloon ontstaat
            Character::query()->update([
                'last_gang_wage_at' => $now,
            ]);

            // Overtollige audit logs van zakkenrollen en bende-donaties na 17:00 opschonen
            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')
                    ->whereIn('action', ['theft', 'gang_donation'])
                    ->where('created_at', '>=', '2026-11-09 17:00:00')
                    ->delete();
                $this->info('   -> Exploit audit logs van vandaag opgeschoond.');
            }

            DB::commit();
            $this->info('====================================================');
            $this->info('RESET SUCCESVOL VOLTOOID!');
            $this->info('====================================================');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Fout tijdens reset: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
