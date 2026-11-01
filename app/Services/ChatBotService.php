<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use Illuminate\Support\Facades\Cache;

class ChatBotService
{
    /**
     * Broadcast an official announcement message in the public clinic chat.
     */
    public function broadcast(string $message, string $channel = 'global'): ?ChatMessage
    {
        $botCharacter = Character::whereHas('user', function ($query) {
            $query->where(function ($sub) {
                $sub->whereIn('role', ['owner', 'admin'])->orWhere('is_staff', true);
            });
        })->orderBy('id')->first() ?? Character::orderBy('id')->first();

        if (! $botCharacter) {
            return null;
        }

        $chatMsg = ChatMessage::create([
            'character_id' => $botCharacter->id,
            'channel' => $channel,
            'message' => $message,
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        Cache::put("chat_last_id_{$channel}", (int) $chatMsg->id, now()->addHours(24));

        return $chatMsg;
    }

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

    /**
     * Send an official liquidation notification from the Kliniek Directie as a private message.
     */
    public function sendLiquidationNotice(Character $character, string $message, array $metadata = []): DirectMessage
    {
        return DirectMessage::create([
            'sender_id' => null, // Kliniek Directie Bot
            'recipient_id' => $character->id,
            'type' => 'liquidation',
            'message' => $message,
            'metadata' => array_merge([
                'title' => 'Liquidatie Incidentenrapport',
                'source' => 'kliniek_directie',
            ], $metadata),
            'is_read' => false,
        ]);
    }

    /**
     * Send liquidation notification to all involved parties via private message from the Kliniek Directie.
     *
     * @param  iterable<Character>|Character  $parties
     */
    public function notifyLiquidationParties(iterable|Character $parties, string $message, array $metadata = []): void
    {
        $recipients = $parties instanceof Character ? collect([$parties]) : collect($parties);

        foreach ($recipients->unique('id') as $character) {
            if ($character instanceof Character) {
                $this->sendLiquidationNotice($character, $message, $metadata);
            }
        }
    }
}
