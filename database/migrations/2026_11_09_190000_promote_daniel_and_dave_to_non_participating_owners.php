<?php

use App\Models\Character;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promote staff accounts for Daniel and Dave to exempt owners and clear gameplay state.
     */
    public function up(): void
    {
        if (Schema::hasTable('characters') && ! Schema::hasColumn('characters', 'is_game_exempt')) {
            Schema::table('characters', function (Blueprint $table): void {
                $table->boolean('is_game_exempt')->default(false)->index();
            });
        }

        if (! Schema::hasTable('users') || ! Schema::hasTable('characters')) {
            return;
        }

        DB::transaction(function (): void {
            $staffUsers = DB::table('users')
                ->where('is_staff', true)
                ->orWhereIn('role', ['owner', 'admin'])
                ->get();

            $charactersByUserId = DB::table('characters')
                ->whereIn('user_id', $staffUsers->pluck('id')->all())
                ->get()
                ->keyBy('user_id');

            $knownOwnerEmails = [
                'danielunutan@icloud.com',
                'admin@tbskliniek.nl',
                'daniel@tbs-kliniek.nl',
                'daniel@tbskliniek.nl',
                'dave@tbskliniek.nl',
                'dave@tbs-kliniek.nl',
            ];

            $targetUserIds = [];

            foreach ($staffUsers as $staffUser) {
                if (($staffUser->role ?? null) === 'owner') {
                    $targetUserIds[] = (int) $staffUser->id;

                    continue;
                }

                $email = mb_strtolower(trim((string) ($staffUser->email ?? '')));
                $userName = mb_strtolower(trim((string) ($staffUser->name ?? '')));
                $charRow = $charactersByUserId->get($staffUser->id);
                $charName = mb_strtolower(trim((string) ($charRow->name ?? '')));
                $charUsername = mb_strtolower(trim((string) ($charRow->username ?? '')));

                $matchesEmail = in_array($email, $knownOwnerEmails, true)
                    || str_starts_with($email, 'daniel@')
                    || str_starts_with($email, 'dave@');

                $matchesName = $this->matchesOwnerIdentity($userName)
                    || $this->matchesOwnerIdentity($charName)
                    || $this->matchesOwnerIdentity($charUsername);

                if ($matchesEmail || $matchesName) {
                    $targetUserIds[] = (int) $staffUser->id;
                }
            }

            $targetUserIds = array_values(array_unique($targetUserIds));

            if (! empty($targetUserIds)) {
                DB::table('users')
                    ->whereIn('id', $targetUserIds)
                    ->update([
                        'role' => 'owner',
                        'is_staff' => true,
                        'updated_at' => now(),
                    ]);
            }

            // Mark excluded system characters as game-exempt too so queries use one source of truth.
            DB::table('characters')
                ->whereIn('name', Character::EXCLUDED_SYSTEM_NAMES)
                ->update([
                    'is_game_exempt' => true,
                    'updated_at' => now(),
                ]);

            if (empty($targetUserIds)) {
                return;
            }

            $ownerCharacters = DB::table('characters')
                ->whereIn('user_id', $targetUserIds)
                ->get();

            if ($ownerCharacters->isEmpty()) {
                return;
            }

            $ownerCharIds = $ownerCharacters->pluck('id')->map(fn ($id): int => (int) $id)->all();

            foreach ($ownerCharacters as $ownerChar) {
                $this->removeCharacterFromGang((int) $ownerChar->id, $ownerChar->gang_id ? (int) $ownerChar->gang_id : null, (string) ($ownerChar->gang_role ?? ''), $ownerCharIds);
            }

            $characterColumns = array_flip(Schema::getColumnListing('characters'));
            $desiredResetValues = [
                'is_game_exempt' => true,
                'title' => 'Eigenaar',
                'gang_id' => null,
                'gang_role' => 'none',
                'last_gang_wage_at' => null,
                'last_allowance_at' => null,
                'level' => 0,
                'xp' => 0,
                'total_xp' => 0,
                'respect' => 0,
                'today_respect' => 0,
                'today_respect_date' => null,
                'cash' => 0,
                'bank' => 0,
                'health' => 100,
                'max_health' => 100,
                'energy' => 100,
                'max_energy' => 100,
                'fights_won' => 0,
                'fights_lost' => 0,
                'thefts_succeeded' => 0,
                'thefts_failed' => 0,
                'kills_count' => 0,
                'deaths_count' => 0,
                'daily_attacks_count' => 0,
                'daily_deaths_count' => 0,
                'missions_completed' => 0,
                'prison_sentences' => 0,
                'prison_escapes' => 0,
                'breakout_attempts' => 0,
                'dagbesteding_shifts_completed' => 0,
                'dagbesteding_earned_total' => 0,
                'hacks_completed_total' => 0,
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
                'coma_until' => null,
                'sedated_until' => null,
                'protection_until' => null,
                'cash_protection_until' => null,
                'updated_at' => now(),
            ];

            $filteredResetValues = array_intersect_key($desiredResetValues, $characterColumns);

            DB::table('characters')
                ->whereIn('id', $ownerCharIds)
                ->update($filteredResetValues);

            $this->cleanActiveGameplayRecords($ownerCharIds);
        });

        $this->flushRankingCaches();
    }

    /**
     * Remove the game exemption column on rollback.
     */
    public function down(): void
    {
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'is_game_exempt')) {
            Schema::table('characters', function (Blueprint $table): void {
                $table->dropColumn('is_game_exempt');
            });
        }
    }

    /**
     * Check whether a lowercased staff name matches Daniel or Dave.
     */
    private function matchesOwnerIdentity(string $normalizedName): bool
    {
        if ($normalizedName === '') {
            return false;
        }

        $exactMatches = ['daniel', 'daniël', 'dave'];
        if (in_array($normalizedName, $exactMatches, true)) {
            return true;
        }

        foreach ($exactMatches as $prefix) {
            if (str_starts_with($normalizedName, $prefix.' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove an exempt owner character from its gang and transfer leadership or disband empty gangs.
     *
     * @param  list<int>  $ownerCharIds
     */
    private function removeCharacterFromGang(int $charId, ?int $gangId, string $gangRole, array $ownerCharIds): void
    {
        if (! Schema::hasTable('gangs')) {
            return;
        }

        if (Schema::hasColumn('gangs', 'personal_shopper')) {
            DB::table('gangs')
                ->where('personal_shopper', $charId)
                ->update(['personal_shopper' => null]);
        }

        if (Schema::hasTable('character_vehicles')) {
            DB::table('character_vehicles')
                ->where('character_id', $charId)
                ->where('in_gang_garage', true)
                ->where('is_gang_owned', false)
                ->update([
                    'in_gang_garage' => false,
                    'gang_id' => null,
                    'stored_at' => null,
                    'stored_by_character_id' => null,
                ]);
        }

        if (! $gangId) {
            return;
        }

        $gang = DB::table('gangs')->where('id', $gangId)->first();
        if (! $gang) {
            return;
        }

        $isLeader = ($gangRole === 'leader') || ((int) ($gang->leader_character_id ?? 0) === $charId);
        if (! $isLeader) {
            return;
        }

        $nextLeader = DB::table('characters')
            ->where('gang_id', $gangId)
            ->whereNotIn('id', $ownerCharIds)
            ->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES)
            ->orderByRaw("CASE WHEN gang_role = 'co_leader' THEN 0 ELSE 1 END")
            ->orderByDesc('respect')
            ->orderBy('id')
            ->first();

        if ($nextLeader) {
            DB::table('gangs')
                ->where('id', $gangId)
                ->update([
                    'leader_character_id' => $nextLeader->id,
                    'updated_at' => now(),
                ]);

            DB::table('characters')
                ->where('id', $nextLeader->id)
                ->update([
                    'gang_role' => 'leader',
                    'updated_at' => now(),
                ]);

            return;
        }

        // Disband the gang when no non-exempt members remain.
        if (Schema::hasTable('character_vehicles')) {
            DB::table('character_vehicles')
                ->where('gang_id', $gangId)
                ->where('in_gang_garage', true)
                ->update([
                    'in_gang_garage' => false,
                    'gang_id' => null,
                    'is_gang_owned' => false,
                    'stored_at' => null,
                    'stored_by_character_id' => null,
                ]);
        }

        if (Schema::hasTable('control_points') && Schema::hasColumn('control_points', 'current_gang_id')) {
            DB::table('control_points')
                ->where('current_gang_id', $gangId)
                ->update([
                    'current_gang_id' => null,
                    'current_character_id' => null,
                    'captured_at' => null,
                ]);
        }

        DB::table('characters')
            ->where('gang_id', $gangId)
            ->update([
                'gang_id' => null,
                'gang_role' => 'none',
            ]);

        DB::table('gangs')->where('id', $gangId)->delete();
    }

    /**
     * Clean open PvP records, bounties, and leaderboard scores for exempt owner characters.
     *
     * @param  list<int>  $ownerCharIds
     */
    private function cleanActiveGameplayRecords(array $ownerCharIds): void
    {
        if (Schema::hasTable('bounties')) {
            $openBounties = DB::table('bounties')
                ->where('is_claimed', false)
                ->whereIn('target_character_id', $ownerCharIds)
                ->get();

            foreach ($openBounties as $bounty) {
                $placerId = (int) ($bounty->placed_by_character_id ?? 0);
                $amount = (int) ($bounty->amount ?? 0);
                if ($placerId > 0 && $amount > 0 && ! in_array($placerId, $ownerCharIds, true)) {
                    DB::table('characters')
                        ->where('id', $placerId)
                        ->increment('cash', $amount);
                }
            }

            DB::table('bounties')
                ->where('is_claimed', false)
                ->where(function ($q) use ($ownerCharIds): void {
                    $q->whereIn('target_character_id', $ownerCharIds)
                        ->orWhereIn('placed_by_character_id', $ownerCharIds);
                })
                ->delete();
        }

        if (Schema::hasTable('blood_feuds')) {
            DB::table('blood_feuds')
                ->whereIn('attacker_id', $ownerCharIds)
                ->orWhereIn('victim_id', $ownerCharIds)
                ->delete();
        }

        if (Schema::hasTable('arm_wrestle_matches')) {
            DB::table('arm_wrestle_matches')
                ->where('status', 'open')
                ->whereIn('challenger_id', $ownerCharIds)
                ->delete();
        }

        if (Schema::hasTable('character_arm_wrestle_stats')) {
            DB::table('character_arm_wrestle_stats')
                ->whereIn('character_id', $ownerCharIds)
                ->delete();
        }

        if (Schema::hasTable('control_points')) {
            DB::table('control_points')
                ->whereIn('current_character_id', $ownerCharIds)
                ->update([
                    'current_character_id' => null,
                    'captured_at' => null,
                ]);
        }

        if (Schema::hasTable('marketplace_listings')) {
            DB::table('marketplace_listings')
                ->where('is_sold', false)
                ->whereIn('seller_id', $ownerCharIds)
                ->delete();
        }

        if (Schema::hasTable('gang_join_requests') && Schema::hasColumn('gang_join_requests', 'character_id')) {
            DB::table('gang_join_requests')
                ->whereIn('character_id', $ownerCharIds)
                ->delete();
        }

        $scoreTables = [
            'daily_battle_scores',
            'event_cash_earnings',
            'event_drug_productions',
            'event_vault_crackings',
            'event_hustle_entries',
            'halloween_quest_progress',
        ];

        foreach ($scoreTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'character_id')) {
                DB::table($tableName)->whereIn('character_id', $ownerCharIds)->delete();
            }
        }
    }

    /**
     * Clear cached leaderboard keys so exempt owners disappear immediately after migration.
     */
    private function flushRankingCaches(): void
    {
        try {
            foreach ([5, 10, 15, 25, 50] as $limit) {
                Cache::forget('tbs_top_rankings_'.$limit);
            }
            foreach (['level', 'respect', 'wealth', 'crimes', 'dagbesteding', 'discipline', 'hacks', 'missions'] as $type) {
                Cache::forget("tbs_leaderboard_{$type}_v5");
                Cache::forget("tbs_leaderboard_{$type}_v4");
                Cache::forget("tbs_leaderboard_{$type}");
            }
        } catch (Throwable) {
            // Skip cache clearing when the cache driver is unavailable during CLI bootstrap.
        }
    }
};
