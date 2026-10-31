<?php

use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\GameNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Synchronize, reconstruct, and upgrade all historical liquidation messages into private Kliniek Directie messages for all involved parties.
     */
    public function up(): void
    {
        if (! Schema::hasTable('direct_messages')) {
            return;
        }

        // 1. Transfer any remaining public liquidation messages from chat_messages to direct_messages
        if (Schema::hasTable('chat_messages')) {
            $liquidationChatMessages = ChatMessage::where(function ($channelQuery) {
                $channelQuery->where('channel', 'global')
                    ->orWhereNull('channel')
                    ->orWhere('channel', 'plein');
            })
                ->where(function ($query) {
                    $query->where('message', 'like', '%LIQUIDATIE%')
                        ->orWhere('message', 'like', '%liquidatie%')
                        ->orWhere('message', 'like', '%BLOEDWRAAK VOLTROKKEN%')
                        ->orWhere('message', 'like', '%geliquideerd%')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('message', 'like', '%NOODGEVAL OP DE AFDELING%')
                                ->where('message', 'like', '%neergeschoten%');
                        });
                })
                ->get();

            foreach ($liquidationChatMessages as $chatMsg) {
                $text = $chatMsg->message;
                $attackerName = null;
                $defenderName = null;

                if (preg_match('/LIQUIDATIE OP ([^:]+):\s+(.+?)\s+heeft\s+(.+?)\s+neergeschoten/ui', $text, $m)) {
                    $attackerName = trim($m[2]);
                    $defenderName = trim($m[3]);
                } elseif (preg_match('/BLOEDWRAAK VOLTROKKEN:\s+(.+?)\s+heeft\s+(.+?)\s+op\s+(.+?)\s+geliquideerd/ui', $text, $m)) {
                    $attackerName = trim($m[1]);
                    $defenderName = trim($m[2]);
                } elseif (preg_match('/NOODGEVAL OP DE AFDELING:\s+(.+?)\s+heeft\s+(.+?)\s+zojuist\s+neergeschoten/ui', $text, $m)) {
                    $attackerName = trim($m[1]);
                    $defenderName = trim($m[2]);
                } elseif (preg_match('/(.+?)\s+heeft\s+(.+?)\s+(?:neergeschoten|geliquideerd)/ui', $text, $m)) {
                    $attackerName = trim($m[1]);
                    $defenderName = trim($m[2]);
                }

                $recipients = collect();

                if ($attackerName) {
                    $attacker = Character::where('name', $attackerName)->first()
                        ?? Character::whereRaw('LOWER(name) = ?', [strtolower($attackerName)])->first();
                    if ($attacker) {
                        $recipients->push($attacker);
                    }
                }

                if ($defenderName) {
                    $defender = Character::where('name', $defenderName)->first()
                        ?? Character::whereRaw('LOWER(name) = ?', [strtolower($defenderName)])->first();
                    if ($defender) {
                        $recipients->push($defender);
                    }
                }

                foreach ($recipients->unique('id') as $recipient) {
                    $alreadyExists = DirectMessage::where('recipient_id', $recipient->id)
                        ->whereNull('sender_id')
                        ->where(function ($q) use ($text, $chatMsg) {
                            $q->where('message', $text)
                                ->orWhereBetween('created_at', [
                                    $chatMsg->created_at->copy()->subSeconds(5),
                                    $chatMsg->created_at->copy()->addSeconds(5),
                                ]);
                        })
                        ->exists();

                    if (! $alreadyExists) {
                        DirectMessage::create([
                            'sender_id' => null,
                            'recipient_id' => $recipient->id,
                            'type' => 'liquidation',
                            'message' => $text,
                            'metadata' => [
                                'title' => 'Liquidatie Incidentenrapport',
                                'source' => 'pleinchat_migration',
                                'legacy_chat_id' => $chatMsg->id,
                                'attacker_name' => $attackerName,
                                'defender_name' => $defenderName,
                            ],
                            'is_read' => true,
                            'created_at' => $chatMsg->created_at,
                            'updated_at' => $chatMsg->updated_at,
                        ]);
                    }
                }

                $chatMsg->delete();
            }
        }

        // 2. Reconstruct and backfill historical liquidations from game_notifications (type 'skull')
        // This ensures both attacker and victim can see their historical liquidation reports even if chat_messages were already deleted earlier.
        if (Schema::hasTable('game_notifications')) {
            $historicalSkullNotifications = GameNotification::where('type', 'skull')->get();

            foreach ($historicalSkullNotifications as $notif) {
                $defender = Character::find($notif->character_id);
                if (! $defender) {
                    continue;
                }

                $attacker = null;
                $reportHeadline = null;
                $medicalNote = null;

                if (preg_match('/^(.+?)\s+heeft je\s+op\s+(.+?)\s+\((?:\+25% Sluipaanval-bonus|Afstandssalvo vanaf [^)]+)\)\s+geliquideerd met\s+(.+?)!\s+Er is\s+€([\d\.]+)\s+contant \(45%\)\s*\+\s*€([\d\.]+)\s+bank \(7%\)\s+geplunderd\.\s*(.*)$/usi', $notif->message, $match)) {
                    $attackerName = trim($match[1]);
                    $wing = trim($match[2]);
                    $shotType = trim($match[3]);
                    $cashInt = (int) str_replace('.', '', $match[4]);
                    $bankInt = (int) str_replace('.', '', $match[5]);
                    $totalFmt = '€'.number_format($cashInt + $bankInt, 0, ',', '.');
                    $medicalNote = trim($match[6]);

                    $attacker = Character::where('name', $attackerName)->first()
                        ?? Character::whereRaw('LOWER(name) = ?', [strtolower($attackerName)])->first();
                    $resolvedAttackerName = $attacker ? $attacker->name : $attackerName;

                    $reportHeadline = "🎯 LIQUIDATIE OP {$wing}: {$resolvedAttackerName} heeft {$defender->name} neergeschoten met {$shotType} en {$totalFmt} (45% contant + 7% bank) buitgemaakt!";
                } elseif (preg_match('/^(.+?)\s+heeft je\s+(.+?)\s+geliquideerd met\s+(.+?)!\s+Er is\s+(.+?)\s+geplunderd\.\s*(.*)$/usi', $notif->message, $match)) {
                    $attackerName = trim($match[1]);
                    $wing = trim(preg_replace('/^op\s+/i', '', preg_replace('/\s*\([^)]*\)\s*$/', '', $match[2])));
                    $shotType = trim($match[3]);
                    $lootText = trim($match[4]);
                    $medicalNote = trim($match[5]);

                    $attacker = Character::where('name', $attackerName)->first()
                        ?? Character::whereRaw('LOWER(name) = ?', [strtolower($attackerName)])->first();
                    $resolvedAttackerName = $attacker ? $attacker->name : $attackerName;

                    $reportHeadline = "🎯 LIQUIDATIE OP {$wing}: {$resolvedAttackerName} heeft {$defender->name} neergeschoten met {$shotType} ({$lootText} geplunderd)!";
                } elseif (preg_match('/^(.+?)\s+heeft je neergeschoten met\s+(\d+)\s+kogels!.*?\((€[\d\.]+)\)\s+is geplunderd/usi', $notif->message, $match)) {
                    $attackerName = trim($match[1]);
                    $bullets = trim($match[2]);
                    $cashFmt = trim($match[3]);

                    $attacker = Character::where('name', $attackerName)->first()
                        ?? Character::whereRaw('LOWER(name) = ?', [strtolower($attackerName)])->first();
                    $resolvedAttackerName = $attacker ? $attacker->name : $attackerName;

                    $reportHeadline = "🎯 LIQUIDATIE: {$resolvedAttackerName} heeft {$defender->name} neergeschoten met {$bullets} kogels en {$cashFmt} contant geplunderd!";
                }

                if (! $reportHeadline) {
                    continue;
                }

                $defenderMessage = $medicalNote ? "{$reportHeadline}\n\n🏥 {$medicalNote}" : $reportHeadline;

                // Upgrade defender's DM to 'liquidation' and deduplicate any extra DM at the same timestamp
                $defenderDm = DirectMessage::where('recipient_id', $defender->id)
                    ->whereNull('sender_id')
                    ->whereBetween('created_at', [
                        $notif->created_at->copy()->subSeconds(5),
                        $notif->created_at->copy()->addSeconds(5),
                    ])
                    ->orderByRaw("CASE WHEN type = 'liquidation' THEN 0 ELSE 1 END")
                    ->first();

                if ($defenderDm) {
                    $defenderDm->update([
                        'type' => 'liquidation',
                        'message' => $defenderMessage,
                        'metadata' => array_merge($defenderDm->metadata ?? [], [
                            'title' => 'Liquidatie Incidentenrapport',
                            'game_notification_id' => $notif->id,
                        ]),
                    ]);

                    DirectMessage::where('recipient_id', $defender->id)
                        ->whereNull('sender_id')
                        ->where('id', '!=', $defenderDm->id)
                        ->whereIn('type', ['skull', 'liquidation'])
                        ->whereBetween('created_at', [
                            $notif->created_at->copy()->subSeconds(5),
                            $notif->created_at->copy()->addSeconds(5),
                        ])
                        ->delete();
                } else {
                    DirectMessage::create([
                        'sender_id' => null,
                        'recipient_id' => $defender->id,
                        'type' => 'liquidation',
                        'message' => $defenderMessage,
                        'metadata' => [
                            'title' => 'Liquidatie Incidentenrapport',
                            'source' => 'notification_backfill',
                            'game_notification_id' => $notif->id,
                        ],
                        'is_read' => (bool) $notif->is_read,
                        'created_at' => $notif->created_at,
                        'updated_at' => $notif->updated_at,
                    ]);
                }

                // Ensure attacker also has the 'liquidation' DM in their Kliniek Directie conversation
                if ($attacker) {
                    $attackerDm = DirectMessage::where('recipient_id', $attacker->id)
                        ->whereNull('sender_id')
                        ->where('type', 'liquidation')
                        ->whereBetween('created_at', [
                            $notif->created_at->copy()->subSeconds(5),
                            $notif->created_at->copy()->addSeconds(5),
                        ])
                        ->first();

                    if ($attackerDm) {
                        if (str_starts_with($attackerDm->message, '🎯 LIQUIDATIE OP 🚨')) {
                            $attackerDm->update(['message' => $reportHeadline]);
                        }
                    } else {
                        DirectMessage::create([
                            'sender_id' => null,
                            'recipient_id' => $attacker->id,
                            'type' => 'liquidation',
                            'message' => $reportHeadline,
                            'metadata' => [
                                'title' => 'Liquidatie Incidentenrapport',
                                'source' => 'notification_backfill',
                                'attacker_name' => $attacker->name,
                                'defender_name' => $defender->name,
                            ],
                            'is_read' => true,
                            'created_at' => $notif->created_at,
                            'updated_at' => $notif->updated_at,
                        ]);
                    }
                }
            }
        }

        // 3. Backfill historical avenged blood feuds for all involved parties (avenger, target, and original victim)
        if (Schema::hasTable('blood_feuds')) {
            $avengedFeuds = BloodFeud::where('is_avenged', true)->whereNotNull('avenged_by_id')->get();

            foreach ($avengedFeuds as $feud) {
                $avenger = Character::find($feud->avenged_by_id);
                $target = Character::find($feud->attacker_id);
                $originalVictim = $feud->victim_id ? Character::find($feud->victim_id) : null;

                if (! $avenger || ! $target) {
                    continue;
                }

                $feudTime = $feud->avenged_at ?? $feud->updated_at ?? $feud->created_at;
                $feudLine = "🩸 BLOEDWRAAK VOLTROKKEN: {$avenger->name} heeft {$target->name} geliquideerd en de bende-vete gewroken!";

                $parties = collect([$avenger, $target, $originalVictim])->filter()->unique('id');
                foreach ($parties as $party) {
                    $existingDm = DirectMessage::where('recipient_id', $party->id)
                        ->whereNull('sender_id')
                        ->where('type', 'liquidation')
                        ->whereBetween('created_at', [
                            $feudTime->copy()->subSeconds(10),
                            $feudTime->copy()->addSeconds(10),
                        ])
                        ->first();

                    if ($existingDm) {
                        if (! str_contains($existingDm->message, 'BLOEDWRAAK VOLTROKKEN')) {
                            $existingDm->update([
                                'message' => $existingDm->message."\n\n".$feudLine,
                            ]);
                        }
                    } else {
                        DirectMessage::create([
                            'sender_id' => null,
                            'recipient_id' => $party->id,
                            'type' => 'liquidation',
                            'message' => $feudLine,
                            'metadata' => [
                                'title' => 'Liquidatie Incidentenrapport',
                                'subtype' => 'blood_feud',
                                'source' => 'blood_feud_backfill',
                            ],
                            'is_read' => true,
                            'created_at' => $feudTime,
                            'updated_at' => $feudTime,
                        ]);
                    }
                }
            }
        }

        // 4. Upgrade any remaining legacy 'skull' DirectMessages to 'liquidation'
        DirectMessage::whereNull('sender_id')
            ->where('type', 'skull')
            ->each(function (DirectMessage $dm) {
                $cleanMsg = preg_replace('/^📢\s*/u', '🎯 ', $dm->message);
                $dm->update([
                    'type' => 'liquidation',
                    'message' => $cleanMsg,
                    'metadata' => array_merge($dm->metadata ?? [], [
                        'title' => 'Liquidatie Incidentenrapport',
                    ]),
                ]);
            });

        // 5. Refresh public chat cache
        if (Schema::hasTable('chat_messages')) {
            $latest = ChatMessage::where('channel', 'global')->latest('id')->first();
            if ($latest) {
                Cache::put('chat_last_id_global', (int) $latest->id, now()->addHours(24));
            } else {
                Cache::forget('chat_last_id_global');
            }
            Cache::forget('chat_deleted_ids_global');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way synchronization of private liquidation notices.
    }
};
