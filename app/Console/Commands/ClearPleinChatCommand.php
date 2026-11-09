<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('tbs:clear-plein-chat')]
#[Description('Maakt de complete Plein Chat en alle bijbehorende caches 100% leeg voor een schone start')]
class ClearPleinChatCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=============================================');
        $this->info('TBS KLINIEK - PLEIN CHAT LEEGMAKEN');
        $this->info('=============================================');

        $beforeCount = ChatMessage::count();
        $this->line("Aantal berichten in ChatMessage database voor schoning: {$beforeCount}");

        // 1. Verwijder alle chatberichten
        if (Schema::hasTable('chat_messages')) {
            DB::table('chat_messages')->delete();
            $this->info('   -> Tabel [chat_messages] succesvol geleegd (0 berichten over).');
        }

        // 2. Clear alle chat-specifieke caches
        Cache::forget('chat_last_id_global');
        Cache::forget('chat_deleted_ids_global');
        Cache::forget('all_character_names_list');

        // Gang channels
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget("chat_last_id_gang_{$i}");
            Cache::forget("chat_deleted_ids_gang_{$i}");
        }

        // Volledige cache herstart
        Cache::flush();
        $this->info('   -> Alle chat caches en last_id buffers leeggemaakt.');

        $afterCount = ChatMessage::count();
        $this->info("Huidig aantal berichten in database: {$afterCount}");
        $this->info('=============================================');
        $this->info('PLEIN CHAT EN BENDE CHAT ZIJN NU 100% LEEG!');
        $this->info('=============================================');

        return self::SUCCESS;
    }
}
