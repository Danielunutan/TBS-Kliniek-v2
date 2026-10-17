<?php

namespace App\Services;

use App\Models\Character;
use App\Models\DirectMessage;

class ChatBotService
{
    /**
     * Send an interactive Level Up congratulation card to the player's chat.
     */
    public function sendLevelUp(Character $character, int $newLevel, array $rewards = []): DirectMessage
    {
        $rewardSummary = ! empty($rewards) ? implode(', ', $rewards) : 'Maximale energie & gezondheid hersteld, nieuwe kansen ontgrendeld!';

        return DirectMessage::create([
            'sender_id' => null, // Kliniek Directie Bot
            'recipient_id' => $character->id,
            'type' => 'level_up',
            'message' => "🎉 Gefeliciteerd {$character->name}! Je bent gepromoveerd naar Level {$newLevel}! Blijf respect verdienen op de afdeling.",
            'metadata' => [
                'level' => $newLevel,
                'rewards' => $rewardSummary,
                'unlocked' => "Level {$newLevel} Privileges",
            ],
            'is_read' => false,
        ]);
    }

    /**
     * Send an interactive golden vault code voucher card to the player's chat.
     */
    public function sendVaultCode(Character $character, string $code, string $reason, string $rewards = '€ 15.000 bankgeld en 3 Sterren ⭐'): DirectMessage
    {
        return DirectMessage::create([
            'sender_id' => null, // Kliniek Directie Bot
            'recipient_id' => $character->id,
            'type' => 'kluiscode',
            'message' => "🔐 Kluiscode Voucher Ontvangen: {$code} ({$reason}). Wissel direct in op de MedicijnKluis!",
            'metadata' => [
                'code' => $code,
                'reason' => $reason,
                'rewards' => $rewards,
            ],
            'is_read' => false,
        ]);
    }

    /**
     * Send an official system notice from the Kliniek Directie.
     */
    public function sendSystemNotice(Character $character, string $title, string $message, ?string $actionUrl = null): DirectMessage
    {
        return DirectMessage::create([
            'sender_id' => null, // Kliniek Directie Bot
            'recipient_id' => $character->id,
            'type' => 'system',
            'message' => $message,
            'metadata' => [
                'title' => $title,
                'action_url' => $actionUrl,
            ],
            'is_read' => false,
        ]);
    }
}
