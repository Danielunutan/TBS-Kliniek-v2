<?php

use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Support\GangPropertyCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // 1. Purge any expired NPCs across all gangs
        try {
            GangNpc::purgeAllExpired();
        } catch (Throwable $e) {
            Log::warning('Error during GangNpc::purgeAllExpired in migration: '.$e->getMessage());
        }

        // 2. Find any active groups with count > 5
        $excessGroups = DB::table('gang_npcs')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->selectRaw('gang_id, npc_key, count(id) as total_count')
            ->groupBy('gang_id', 'npc_key')
            ->havingRaw('count(id) > ?', [GangNpc::MAX_ACTIVE_PER_TYPE])
            ->get();

        foreach ($excessGroups as $group) {
            $gangId = (int) $group->gang_id;
            $npcKey = (string) $group->npc_key;

            DB::transaction(function () use ($gangId, $npcKey) {
                /** @var Gang|null $lockedGang */
                $lockedGang = Gang::where('id', $gangId)->lockForUpdate()->first();
                if (! $lockedGang) {
                    return;
                }

                $activeInstances = GangNpc::where('gang_id', $gangId)
                    ->where('npc_key', $npcKey)
                    ->active()
                    ->orderBy('hired_at')
                    ->orderBy('id')
                    ->get();

                if ($activeInstances->count() <= GangNpc::MAX_ACTIVE_PER_TYPE) {
                    return;
                }

                $kept = $activeInstances->take(GangNpc::MAX_ACTIVE_PER_TYPE);
                $excess = $activeInstances->slice(GangNpc::MAX_ACTIVE_PER_TYPE);

                $npcData = GangPropertyCatalog::getNpc($npcKey);
                $unitCost = $npcData ? (int) $npcData['cost'] : 0;
                $npcName = $npcData ? $npcData['name'] : $npcKey;

                $totalRefund = 0;
                $deletedIds = [];

                foreach ($excess as $instance) {
                    $totalRefund += $unitCost;
                    $deletedIds[] = $instance->id;
                }

                if (! empty($deletedIds)) {
                    GangNpc::whereIn('id', $deletedIds)->delete();
                }

                if ($totalRefund > 0) {
                    $lockedGang->treasury = (int) $lockedGang->treasury + $totalRefund;
                    if (Schema::hasColumn('gangs', 'protected_treasury')) {
                        $lockedGang->protected_treasury = (int) ($lockedGang->protected_treasury ?? 0) + $totalRefund;
                    }
                    $lockedGang->save();

                    $formattedRefund = number_format($totalRefund, 0, ',', '.');
                    $excessCount = count($deletedIds);

                    if ($lockedGang->leader_character_id) {
                        try {
                            GameNotification::create([
                                'character_id' => $lockedGang->leader_character_id,
                                'type' => 'gang_npc_rebalance',
                                'icon' => 'fa-users-gear',
                                'title' => "⚖️ NPC Specialist Limiet & Restitutie: {$npcName}",
                                'message' => "Er geldt voortaan een limiet van maximaal 5 actieve contracten per specialist per 30 minuten. Voor {$excessCount} overtollige contracten van {$npcName} is zojuist €{$formattedRefund} teruggestort in jullie bende kas!",
                            ]);
                        } catch (Throwable) {
                            // Ignore if notification cannot be sent
                        }
                    }

                    Log::info("Gang [{$lockedGang->name}] (ID: {$lockedGang->id}) refunded €{$formattedRefund} for {$excessCount} excess '{$npcKey}' NPCs above 5 limit.");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // irreversible rebalance refund
    }
};
