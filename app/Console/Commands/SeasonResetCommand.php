<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeasonResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbs:season-reset {--dry-run : Simulate the reset without modifying the database} {--force : Force execution without confirmation} {--vip-only : Only reset active VIP status for all characters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset TBS Kliniek for a brand new season while preserving stars, VIP points, gangs, users and news';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('vip-only')) {
            $count = Character::where('is_vip', true)->orWhereNotNull('vip_expires_at')->count();
            Character::query()->update(['is_vip' => false, 'vip_expires_at' => null]);
            $this->info("Alle actieve VIP statussen gereset ({$count} karakters bijgewerkt). Niemand is nu meer VIP.");

            return 0;
        }

        $isDryRun = (bool) $this->option('dry-run');

        $this->info('=========================================');
        $this->info('TBS KLINIEK - SEIZOEN RESET PROCEDURE');
        $this->info('=========================================');

        if ($isDryRun) {
            $this->warn('DRY RUN MODUS ACTIEF: Er worden geen wijzigingen opgeslagen.');
        }

        $characterCount = Character::count();
        $this->line("Aantal karakters in database: {$characterCount}");

        if (! $this->option('force') && ! $isDryRun) {
            if (! $this->confirm('Weet je 100% zeker dat je Seizoen 1 wilt starten en alle statistieken wilt resetten?')) {
                $this->warn('Reset geannuleerd.');

                return 0;
            }
        }

        DB::beginTransaction();

        try {
            // 1. Reset all Characters
            $this->info('1. Karakters resetten naar basiswaarden (Sterren, VIP-punten, Gangs en Namen blijven behouden)...');

            $now = now();
            $protectionUntil = null; // Geen blokkade op beroven

            $updateData = [
                // Level & XP
                'level' => 1,
                'xp' => 0,
                'total_xp' => 0,

                // Startgeld
                'cash' => 250,
                'bank' => 1000,

                // Energie & Gezondheid
                'energy' => 100,
                'max_energy' => 100,
                'health' => 100,
                'max_health' => 100,
                'last_energy_refill_at' => $now,

                // RPG Stats
                'respect' => 10,
                'today_respect' => 0,
                'strength' => 10,
                'defense' => 10,
                'stamina' => 10,
                'intelligence' => 10,
                'charisma' => 10,
                'discipline' => 10,

                // Seizoensstatistieken
                'fights_won' => 0,
                'fights_lost' => 0,
                'thefts_succeeded' => 0,
                'thefts_failed' => 0,
                'missions_completed' => 0,
                'prison_sentences' => 0,
                'prison_escapes' => 0,
                'daily_attacks_count' => 0,

                // Vrijlating & Bescherming
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
                'protection_until' => $protectionUntil,

                // Geen VIP bij seizoenstart
                'is_vip' => false,
                'vip_expires_at' => null,

                // Spullen & Mijlpalen
                'bullets' => 50,
                'masks' => 0,
                'vault_tokens' => 5,
                'claimed_level_rewards' => null,
                'unopened_bags' => 0,
                'vest_until' => null,
                'perks' => null,
            ];

            // Only update columns that actually exist in the table
            $columnsInDb = Schema::getColumnListing('characters');
            $filteredUpdates = array_intersect_key($updateData, array_flip($columnsInDb));

            if (! $isDryRun) {
                Character::query()->update($filteredUpdates);
            }
            $this->info("   -> {$characterCount} karakters succesvol gereset.");

            // 2. Clear progress / seasonal tables
            $tablesToTruncate = [
                'character_missions',
                'character_cooldowns',
                'character_items',
                'character_vehicles',
                'character_heists',
                'heist_logs',
                'character_true_crime_states',
                'character_memory_stats',
                'character_connect_four_stats',
                'fights',
                'attacks',
                'daily_battles',
                'daily_battle_scores',
                'loans',
                'bank_transactions',
                'xxl_cases',
                'evening_vault_drops',
                'koffer_logs',
                'marketplace_listings',
                'halloween_quest_progress',
                'arm_wrestling_matches',
                'bounties',
                'blood_feuds',
                'gang_items',
                'gang_properties',
                'gang_npcs',
                'gang_attacks',
                'gang_donations',
                'control_point_logs',
            ];

            $this->info('2. Seizoenstabellen en voortgang leegmaken...');
            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    if (! $isDryRun) {
                        DB::table($table)->delete();
                    }
                    $this->line("   -> Tabel [{$table}] geleegd.");
                }
            }

            // 3. Reset Gangs to 0
            $this->info('3. Alle bendes resetten naar 0 (kas, respect, maskers)...');
            if (Schema::hasTable('gangs')) {
                $gangUpdate = [
                    'treasury' => 0,
                    'respect' => 0,
                    'masks' => 0,
                    'shield_until' => null,
                    'vip_boost_until' => null,
                    'last_attack_at' => null,
                ];
                $gangColumns = Schema::getColumnListing('gangs');
                $filteredGangUpdates = array_intersect_key($gangUpdate, array_flip($gangColumns));
                if (! $isDryRun) {
                    DB::table('gangs')->update($filteredGangUpdates);
                }
                $this->info('   -> Alle bendes gereset naar 0.');
            }

            // 4. Reset Control Points (bendes kunnen ze opnieuw veroveren)
            if (Schema::hasTable('control_points')) {
                $cpUpdates = [
                    'current_character_id' => null,
                    'current_gang_id' => null,
                    'captured_at' => null,
                    'total_captures' => 0,
                    'gang_id' => null,
                    'control_level' => 0,
                    'last_contested_at' => null,
                ];
                $cpColumns = Schema::getColumnListing('control_points');
                $filteredCpUpdates = array_intersect_key($cpUpdates, array_flip($cpColumns));
                if (! $isDryRun && ! empty($filteredCpUpdates)) {
                    DB::table('control_points')->update($filteredCpUpdates);
                }
                $this->info('   -> Controleposten gereset naar neutraal.');
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->warn('DRY RUN VOLTOOID: Wijzigingen zijn teruggedraaid.');
            } else {
                DB::commit();
                $this->info('=========================================');
                $this->info('SEIZOEN 1 RESET MET SUCCES VOLTOOID!');
                $this->info('Alle spelers starten gelijk met behoud van Sterren, VIP Punten & Bendes.');
                $this->info('=========================================');
            }

            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Fout tijdens seizoensreset: '.$e->getMessage());

            return 1;
        }
    }
}
