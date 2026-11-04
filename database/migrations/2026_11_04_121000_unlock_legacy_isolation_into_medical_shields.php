<?php

use App\Models\Character;
use App\Models\CharacterCooldown;
use App\Models\GameNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unlock accidental voluntary isolation locks for characters with legacy protection
     * by syncing medical_shield_until with protection_until so their cell door unlocks
     * while preserving their remaining defense shield. Also clean any accidental isolation cooldowns.
     */
    public function up(): void
    {
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'protection_until')) {
            $now = now();
            $cutoff = '2026-11-04 11:15:00';

            $activeCharacters = Character::whereNotNull('protection_until')
                ->where('protection_until', '>', $now)
                ->get();

            foreach ($activeCharacters as $character) {
                // Check if user deliberately activated voluntary isolation after today's balance deploy
                $hasRecentActivation = false;
                if (Schema::hasTable('game_notifications')) {
                    $hasRecentActivation = GameNotification::where('character_id', $character->id)
                        ->where('type', 'protection')
                        ->where('title', 'like', '%Vrijwillige Isolatie geactiveerd%')
                        ->where('created_at', '>=', $cutoff)
                        ->exists();
                }

                if (! $hasRecentActivation) {
                    $perks = is_array($character->perks) ? $character->perks : [];
                    $perks['medical_shield_until'] = $character->protection_until->timestamp;
                    $character->perks = $perks;
                    $character->save();
                }
            }
        }

        // Clean accidental isolation cooldowns incurred right after the morning deploy
        if (Schema::hasTable('character_cooldowns')) {
            CharacterCooldown::where('action', 'isolation_cooldown')
                ->where('created_at', '>=', '2026-11-04 10:00:00')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: preserving player cell state and non-locking shields is idempotent.
    }
};
