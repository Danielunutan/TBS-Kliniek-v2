<?php

use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Support\GangPropertyCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $maxAllowedBonus = Gang::MAX_NPC_DEFENSE_BONUS_PERCENT; // 100%

        // Retrieve all gangs that have defense NPCs
        $gangs = Gang::whereHas('npcs', function ($q) {
            $q->where('bonus_type', 'defense');
        })->with(['npcs' => function ($q) {
            $q->where('bonus_type', 'defense')->orderBy('hired_at')->orderBy('id');
        }, 'members'])->get();

        foreach ($gangs as $gang) {
            $enforcers = $gang->npcs->where('bonus_type', 'defense')->values();
            $totalBonus = (int) $enforcers->sum('bonus_value');

            if ($totalBonus <= $maxAllowedBonus) {
                continue;
            }

            DB::transaction(function () use ($gang, $enforcers, $totalBonus, $maxAllowedBonus) {
                /** @var Gang $lockedGang */
                $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
                if (! $lockedGang) {
                    return;
                }

                $runningBonus = 0;
                $keptNpcIds = [];
                $removedNpcs = [];
                $totalRefund = 0;

                foreach ($enforcers as $npc) {
                    // If adding this NPC keeps total <= 100%, or if we haven't kept any yet
                    if ($runningBonus + (int) $npc->bonus_value <= $maxAllowedBonus) {
                        $runningBonus += (int) $npc->bonus_value;
                        $keptNpcIds[] = $npc->id;
                    } else {
                        // Excess NPC to remove and refund
                        $npcData = GangPropertyCatalog::getNpc($npc->npc_key);
                        $cost = $npcData ? (int) $npcData['cost'] : 500000;
                        if ($cost <= 0) {
                            $cost = 500000;
                        }

                        $totalRefund += $cost;
                        $removedNpcs[] = [
                            'id' => $npc->id,
                            'name' => $npc->name,
                            'bonus' => $npc->bonus_value,
                            'cost' => $cost,
                        ];
                    }
                }

                // Delete excess NPCs
                $removedIds = array_column($removedNpcs, 'id');
                if (! empty($removedIds)) {
                    GangNpc::whereIn('id', $removedIds)->delete();
                }

                // Ensure kept Enforcers sum to the exact 100% cap if procedural values left a small gap (e.g. 92%)
                if ($runningBonus < $maxAllowedBonus && ! empty($keptNpcIds)) {
                    $shortfall = $maxAllowedBonus - $runningBonus;
                    $lastKeptNpcId = end($keptNpcIds);
                    $lastKeptNpc = GangNpc::find($lastKeptNpcId);
                    if ($lastKeptNpc) {
                        $lastKeptNpc->bonus_value = (int) $lastKeptNpc->bonus_value + $shortfall;
                        $lastKeptNpc->save();
                        $runningBonus = $maxAllowedBonus;
                    }
                }

                // Refund the total cost back into the gang treasury
                if ($totalRefund > 0) {
                    $lockedGang->treasury += $totalRefund;
                    $lockedGang->save();
                }

                $formattedRefund = number_format($totalRefund, 0, ',', '.');
                $removedCount = count($removedNpcs);

                Log::info("Gang [{$lockedGang->name}] Enforcer rebalance: reduced bonus from +{$totalBonus}% to +{$runningBonus}%. Removed {$removedCount} excess NPCs, refunded €{$formattedRefund} to treasury.");

                // 1. Send in-game message to gang leader
                if ($lockedGang->leader_character_id) {
                    $messageContent = "Beste leiding van {$lockedGang->name},\n\n"
                        ."In het kader van de nieuwe Bende Oorlog Balans Update is de maximale verdedigingsbonus door Enforcers per bende gelimiteerd op maximaal **+{$maxAllowedBonus}%** (een maximale verdubbeling van jullie basisverdediging).\n\n"
                        ."Jullie bende beschikte voorheen over een Enforcer-verdedigingsbonus van **+{$totalBonus}%**. Omdat dit boven het nieuwe maximumlimiet lag, zijn er **{$removedCount} overtollige Enforcers** eervol ontslagen.\n\n"
                        ."💰 **100% Bende Kas Compensatie:**\n"
                        ."De volledige aankoopkosten van de ontslagen Enforcers ter waarde van **€{$formattedRefund}** zijn **volledig teruggestort in jullie Bende Kas**!\n\n"
                        ."🛡️ **Jullie Huidige Status:**\n"
                        ."• Resterende Enforcer Verdedigingsbonus: **+{$runningBonus}%** (het maximale limiet)\n"
                        ."• Bende Kas: verhoogd met **+€{$formattedRefund}**\n\n"
                        ."Met het teruggestorte geld in de kas kunnen jullie investeren in andere panden, bende-economie, of extra Power en Bescherming in de Bende Winkel.\n\n"
                        ."Lees het volledige nieuwsbericht op de nieuwspagina voor alle details over de balansupdate.\n\n"
                        ."Met vriendelijke groet,\n"
                        .'De Directie van FPC De Balk';

                    GameMessage::create([
                        'recipient_id' => $lockedGang->leader_character_id,
                        'sender_id' => null,
                        'sender_name' => 'FPC De Balk Directie',
                        'subject' => '⚖️ [Bende Balans Update] Enforcer Limiet & Bende Kas Compensatie',
                        'content' => $messageContent,
                        'type' => 'gang',
                    ]);
                }

                // 2. Send notification to all officers
                $officers = $lockedGang->members()
                    ->where(function ($q) use ($lockedGang) {
                        $q->whereIn('gang_role', ['leader', 'co_leader'])
                            ->orWhere('id', $lockedGang->leader_character_id)
                            ->orWhere('id', $lockedGang->personal_shopper)
                            ->orWhere('id', $lockedGang->money_magnet);
                    })
                    ->get()
                    ->unique('id');

                foreach ($officers as $officer) {
                    GameNotification::create([
                        'character_id' => $officer->id,
                        'type' => 'gang',
                        'icon' => 'fa-scale-balanced',
                        'title' => '⚖️ Bende Balans: €'.$formattedRefund.' Teruggestort',
                        'message' => "Enforcers zijn gelimiteerd tot max +{$maxAllowedBonus}%. {$removedCount} overtollige Enforcers zijn ontslagen en €{$formattedRefund} is teruggestort naar jullie Bende Kas!",
                    ]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Enforcer cap refund and cleanup cannot be automatically reversed to prevent duplicating refunded treasury.
    }
};
