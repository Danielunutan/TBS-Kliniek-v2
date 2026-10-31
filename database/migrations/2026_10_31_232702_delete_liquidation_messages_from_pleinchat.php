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

        // 3. Backfill any historical liquidations recorded in game_notifications (type 'skull') where the attacker was missing a direct report
        if (Schema::hasTable('game_notifications')) {
            $historicalSkullNotifications = GameNotification::where('type', 'skull')->get();

            foreach ($historicalSkullNotifications as $notif) {
                if (preg_match('/^([^\s]+(?: [^\s]+)?)\s+heeft je\s+(.+?)\s+geliquideerd met\s+(.+?)!\s+Er is\s+(.+?)\s+geplunderd/ui', $notif->message, $match)) {
                    $attackerName = trim($match[1]);
                    $attacker = Character::where('name', $attackerName)->first()
                        ?? Character::whereRaw('LOWER(name) = ?', [strtolower($attackerName)])->first();
                    $defender = Character::find($notif->character_id);

                    if ($attacker && $defender) {
                        $hasAttackerDm = DirectMessage::where('recipient_id', $attacker->id)
                            ->whereNull('sender_id')
                            ->where('created_at', $notif->created_at)
                            ->exists();

                        if (! $hasAttackerDm) {
                            $reportText = "🎯 LIQUIDATIE OP {$notif->title}: {$attacker->name} heeft {$defender->name} geliquideerd met {$match[3]} en {$match[4]} geplunderd!";
                            DirectMessage::create([
                                'sender_id' => null,
                                'recipient_id' => $attacker->id,
                                'type' => 'liquidation',
                                'message' => $reportText,
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
        }

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
