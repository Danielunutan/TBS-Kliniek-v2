<?php

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
     * Move existing liquidation messages from the public plaza chat to private messages for involved parties, then purge from pleinchat.
     */
    public function up(): void
    {
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        // 1. Locate all liquidation messages currently in the public plaza chat
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

        // 2. Transfer each historical liquidation message to the involved parties as a private message from Kliniek Directie
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
                            ->orWhere('created_at', $chatMsg->created_at);
                    })
                    ->exists();

                if (! $alreadyExists) {
                    DirectMessage::create([
                        'sender_id' => null, // Kliniek Directie
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

            // Remove from the public chat
            $chatMsg->delete();
        }

        // 3. Backfill and upgrade historical liquidations recorded in game_notifications (type 'skull')
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

                // Ensure defender has a clean 'liquidation' DM (upgrading any 'skull' DM created by GameNotification::booted)
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

        // 4. Refresh public chat cache
        $latest = ChatMessage::where('channel', 'global')->latest('id')->first();
        if ($latest) {
            Cache::put('chat_last_id_global', (int) $latest->id, now()->addHours(24));
        } else {
            Cache::forget('chat_last_id_global');
        }

        Cache::forget('chat_deleted_ids_global');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Public chat cleanup is intentional and does not restore messages to the public plaza.
    }
};
