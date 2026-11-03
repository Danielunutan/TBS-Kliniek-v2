<?php

use App\Models\GameMessage;
use App\Models\Gang;
use App\Models\GangNpc;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('gangs') || ! Schema::hasTable('gang_npcs')) {
            return;
        }

        $maxAllowedBonus = Gang::MAX_NPC_DEFENSE_BONUS_PERCENT; // 100%

        $gangs = Gang::whereHas('npcs', function ($q) {
            $q->where('bonus_type', 'defense');
        })->with(['npcs' => function ($q) {
            $q->where('bonus_type', 'defense')->orderBy('hired_at')->orderBy('id');
        }])->get();

        foreach ($gangs as $gang) {
            $enforcers = $gang->npcs->where('bonus_type', 'defense')->values();
            if ($enforcers->isEmpty()) {
                continue;
            }

            $currentSum = (int) $enforcers->sum('bonus_value');

            // Check if this gang was rebalanced by 2026_11_03_133000 and ended up slightly below 100%
            // due to procedural Enforcer bonus values (e.g. 28 + 31 + 33 = 92%)
            $wasRebalanced = false;
            if (Schema::hasTable('game_messages') && $gang->leader_character_id) {
                $wasRebalanced = GameMessage::where('recipient_id', $gang->leader_character_id)
                    ->where('subject', 'like', '%Enforcer Limiet & Bende Kas Compensatie%')
                    ->exists();
            }

            if ($wasRebalanced && $currentSum > 0 && $currentSum < $maxAllowedBonus) {
                DB::transaction(function () use ($gang, $enforcers, $currentSum, $maxAllowedBonus) {
                    $shortfall = $maxAllowedBonus - $currentSum;
                    /** @var GangNpc|null $lastNpc */
                    $lastNpc = $enforcers->last();
                    if ($lastNpc) {
                        $lastNpc->bonus_value = (int) $lastNpc->bonus_value + $shortfall;
                        $lastNpc->save();
                    }

                    if (Schema::hasTable('game_messages') && $gang->leader_character_id) {
                        $msg = GameMessage::where('recipient_id', $gang->leader_character_id)
                            ->where('subject', 'like', '%Enforcer Limiet & Bende Kas Compensatie%')
                            ->latest('id')
                            ->first();

                        if ($msg) {
                            $msg->content = str_replace(
                                "• Resterende Enforcer Verdedigingsbonus: **+{$currentSum}%**",
                                "• Resterende Enforcer Verdedigingsbonus: **+{$maxAllowedBonus}%**",
                                (string) $msg->content
                            );
                            $msg->save();
                        }
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive normalization to 100% cap.
    }
};
