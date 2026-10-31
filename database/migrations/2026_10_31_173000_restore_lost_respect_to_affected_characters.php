<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore respect to all characters who lost respect due to hitman liquidations,
     * backfire incidents, treatment option deductions, or failed prison escapes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        // 1. Ensure no character has below starting minimum respect (10)
        DB::table('characters')
            ->where('respect', '<', 10)
            ->update(['respect' => 10]);

        $characters = DB::table('characters')->get();

        foreach ($characters as $character) {
            $toRestore = 0;

            // A. Check failed prison escape deduction
            if (! empty($character->is_in_prison) && ! empty($character->prison_escape_attempted)) {
                $toRestore += 1;
            }

            // B. Check treatment question 5 option 'a' deduction
            if (! empty($character->treatment_history)) {
                $historyStr = is_string($character->treatment_history)
                    ? $character->treatment_history
                    : json_encode($character->treatment_history);

                if (str_contains($historyStr, 'Afstand nemend van criminele subculturen')) {
                    $toRestore += 1;
                }
            }

            // C. Check hitman liquidations (previously deducted 8% respect, min 15 per hit)
            $liquidationCount = 0;
            if (Schema::hasTable('game_notifications')) {
                $liquidationCount = DB::table('game_notifications')
                    ->where('character_id', $character->id)
                    ->where(function ($query) {
                        $query->where('type', 'skull')
                            ->orWhere('title', 'LIKE', '%GELIQUIDEERD%');
                    })
                    ->count();
            }

            $bloodFeudVictimCount = 0;
            if (Schema::hasTable('blood_feuds')) {
                $bloodFeudVictimCount = DB::table('blood_feuds')
                    ->where('victim_id', $character->id)
                    ->count();
            }

            $hits = max($liquidationCount, $bloodFeudVictimCount);
            if ($hits > 0) {
                // Restore the 8% compound loss (at least 20 respect per liquidation)
                $currentRespect = (float) ($character->respect ?? 10);
                $compoundLoss = (int) round($currentRespect * (pow(1.08, $hits) - 1));
                $toRestore += max($hits * 20, $compoundLoss);
            }

            // D. Apply the restored respect
            if ($toRestore > 0) {
                DB::table('characters')
                    ->where('id', $character->id)
                    ->increment('respect', $toRestore);

                if (Schema::hasTable('game_notifications')) {
                    DB::table('game_notifications')->insert([
                        'character_id' => $character->id,
                        'type' => 'star',
                        'icon' => 'fa-shield-halved',
                        'title' => '⭐ Respect Hersteld!',
                        'message' => "Wegens een correctie op respectverlies is er +{$toRestore} respect met terugwerkende kracht aan je account toegevoegd.",
                        'is_read' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restoration of lost respect is non-reversible.
    }
};
