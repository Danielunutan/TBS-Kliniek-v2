<?php

use App\Models\DirectMessage;
use App\Models\GameMessage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // One-time graceful migration of past 30 days unread/important game messages into direct messages
        $messages = GameMessage::where('created_at', '>=', now()->subDays(30))
            ->orderBy('id', 'asc')
            ->get();

        foreach ($messages as $msg) {
            $isPlayerDm = $msg->sender_id && $msg->type === 'player';
            $senderId = $isPlayerDm ? $msg->sender_id : null;
            $formattedMessage = (! empty($msg->subject) && ! $isPlayerDm)
                ? "📢 **{$msg->subject}**\n\n{$msg->content}"
                : ($msg->content ?? '');

            // Avoid duplicate insert if already migrated
            $alreadyExists = DirectMessage::where('recipient_id', $msg->recipient_id)
                ->where('created_at', $msg->created_at)
                ->exists();

            if (! $alreadyExists) {
                DirectMessage::create([
                    'sender_id' => $senderId,
                    'recipient_id' => $msg->recipient_id,
                    'type' => $msg->type ?? ($isPlayerDm ? 'chat' : 'system'),
                    'message' => $formattedMessage,
                    'metadata' => [
                        'source' => 'system_migration',
                        'subject' => $msg->subject,
                        'original_sender' => $msg->sender_name ?? 'Directie TBS Kliniek',
                        'game_message_id' => $msg->id,
                    ],
                    'is_read' => (bool) $msg->is_read,
                    'created_at' => $msg->created_at,
                    'updated_at' => $msg->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to prevent deleting actual chats
    }
};
