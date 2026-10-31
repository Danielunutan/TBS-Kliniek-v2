<?php

use App\Models\ChatMessage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Delete all liquidation messages from the public plaza chat.
     */
    public function up(): void
    {
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        // Delete all liquidation and blood feud messages from the public chat channel
        ChatMessage::where(function ($channelQuery) {
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
            ->delete();

        // Refresh the public chat cache so the client view updates cleanly
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
        // Public chat cleanup is intentional and does not restore deleted messages.
    }
};
