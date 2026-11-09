<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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
            if (! $this->confirm('Weet je 100% zeker dat je Seizoen 2 wilt starten en alle statistieken wilt resetten?')) {
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
                'today_respect_date' => null,
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
                'breakout_attempts' => 0,
                'prison_escape_attempted' => false,
                'daily_attacks_count' => 0,
                'daily_attacks_date' => null,
                'kills_count' => 0,
                'deaths_count' => 0,
                'daily_deaths_count' => 0,
                'daily_deaths_date' => null,

                // Vrijlating, Locatie & Bescherming
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
                'protection_until' => $protectionUntil,
                'cash_protection_until' => null,
                'coma_until' => null,
                'current_location' => 'kliniek',
                'leave_until' => null,
                'sedated_until' => null,

                // Behandeling, Dagbesteding & Hacking
                'treatment_score' => 50,
                'treatment_history' => null,
                'dagbesteding_shifts_completed' => 0,
                'dagbesteding_earned_total' => 0,
                'palmed_pills_count' => 0,
                'hacks_completed_total' => 0,
                'last_hack_at' => null,
                'last_gang_wage_at' => $now,
                'last_allowance_at' => null,

                // Spullen, Cel & Mijlpalen (VIP status, VIP-punten, Sterren en Pompoenpitten blijven 100% behouden)
                'bullets' => 50,
                'masks' => 0,
                'vault_tokens' => 5,
                'claimed_level_rewards' => null,
                'unopened_bags' => 0,
                'vest_until' => null,
                'perks' => null,
                'cell_upgrades' => null,
            ];

            // Only update columns that actually exist in the table
            $columnsInDb = Schema::getColumnListing('characters');
            $filteredUpdates = array_intersect_key($updateData, array_flip($columnsInDb));

            if (! $isDryRun) {
                Character::query()->playable()->update($filteredUpdates);
            }
            $this->info("   -> {$characterCount} karakters succesvol gereset.");

            // 2. Clear progress / seasonal tables (Challenge tot vrijdag en Herfstquest blijven behouden)
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
                'character_arm_wrestle_stats',
                'fights',
                'attacks',
                'loans',
                'bank_transactions',
                'xxl_cases',
                'evening_vault_drops',
                'koffer_logs',
                'marketplace_listings',
                'arm_wrestle_matches',
                'bounties',
                'blood_feuds',
                'gang_wars',
                'gang_items',
                'gang_properties',
                'gang_npcs',
                'gang_attacks',
                'gang_donations',
                'gang_requests',
                'gang_alliances',
                'control_point_logs',
                'escape_tunnels',
                'theft_records',
                'training_logs',
                'smuggle_droppings',
                'hacking_logs',
                'profile_respects',
                'profile_visits',
                'event_cash_earnings',
                'event_drug_productions',
                'event_vault_crackings',
                'daily_battle_scores',
                'lottery_tickets',
                // Chat, berichten en meldingen uit het verleden voor een 100% schone start
                'chat_messages',
                'cinema_messages',
                'direct_messages',
                'game_messages',
                'game_notifications',
                'radio_shoutouts',
                'radio_shoutout_likes',
                'guestbook_entries',
                'timeline_posts',
                'timeline_comments',
                'timeline_likes',
            ];

            $this->info('2. Seizoenstabellen en voortgang leegmaken...');
            Schema::disableForeignKeyConstraints();
            try {
                foreach ($tablesToTruncate as $table) {
                    if (Schema::hasTable($table)) {
                        if (! $isDryRun) {
                            if ($table === 'character_items' && Schema::hasTable('items')) {
                                $preserveItemIds = DB::table('items')
                                    ->where('slug', 'like', 'halloween_%')
                                    ->orWhere('slug', 'like', 'autumn_%')
                                    ->orWhere('category', 'special')
                                    ->pluck('id');
                                DB::table('character_items')
                                    ->whereNotIn('item_id', $preserveItemIds)
                                    ->delete();
                            } else {
                                DB::table($table)->delete();
                            }
                        }
                        $this->line("   -> Tabel [{$table}] geleegd (exclusieve event-verzamelobjecten behouden).");
                    }
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            // 3. Reset Gangs to 0 (behouden uitsluitend has_gang_chat, naam, tag en leden)
            $this->info('3. Alle bendes resetten naar 0 (kas, respect, maskers, attack power, protection)...');
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
                if (! $isDryRun) {
                    DB::table('gangs')->update($filteredGangUpdates);
                }
                $this->info('   -> Alle bendes gereset naar 0 (gekochte bende chat blijft behouden).');
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

            // 5. Oude nieuwsberichten opschonen (winnaars en kampioenen behouden)
            $this->info('5. Oude nieuwsberichten opschonen (winnaars en kampioenen behouden)...');
            if (Schema::hasTable('news_articles')) {
                if (! $isDryRun) {
                    $keepIds = DB::table('news_articles')
                        ->where(function ($query) {
                            $query->where('title', 'like', '%winnaar%')
                                ->orWhere('title', 'like', '%uitslag%')
                                ->orWhere('title', 'like', '%loterij%')
                                ->orWhere('title', 'like', '%kampioen%')
                                ->orWhere('content', 'like', '%winnaar%')
                                ->orWhere('slug', 'frisse-start-seizoen-2-sprint-30-dagen')
                                ->orWhere('is_pinned', true);
                        })
                        ->pluck('id');

                    if (Schema::hasTable('news_comments')) {
                        DB::table('news_comments')->whereNotIn('news_article_id', $keepIds)->delete();
                    }

                    DB::table('news_articles')
                        ->whereNotIn('id', $keepIds)
                        ->delete();
                }
                $this->info('   -> Oude artikelen verwijderd, winnaars en loterijberichten behouden.');
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->warn('DRY RUN VOLTOOID: Wijzigingen zijn teruggedraaid.');
            } else {
                DB::commit();
                Cache::flush();
                $this->info('=========================================');
                $this->info('SEIZOEN 2 RESET MET SUCCES VOLTOOID!');
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
