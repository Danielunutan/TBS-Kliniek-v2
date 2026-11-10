<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\ChatBotService;
use Illuminate\Console\Command;
use Throwable;

class BroadcastLotteryDiscountCommand extends Command
{
    protected $signature = 'lottery:broadcast-discount';
    protected $description = 'Verstuur notificatie en push melding naar alle spelers over de 80% korting en trekking om 22:00';

    public function handle(ChatBotService $chatBotService): int
    {
        $this->info('Start versturen loterij notificaties...');

        $title = '🚨 Megapot Trekking om 22.00 uur & 80% Korting!';
        $message = 'Let op: de loterij trekking is vervroegd naar vanavond om 22.00 uur! Alle loten zijn per direct 80% goedkoper (€2.000 contant of 3 VIP Sterren). Grijp nu je kans op de megapot!';

        $characters = Character::all();
        $count = 0;

        foreach ($characters as $char) {
            try {
                GameNotification::create([
                    'character_id' => $char->id,
                    'type' => 'reward',
                    'icon' => 'fa-ticket',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false,
                ]);
                $count++;
            } catch (Throwable $e) {
                $this->error("Fout bij speler {$char->id}: " . $e->getMessage());
            }
        }

        $this->info("Notificatie verstuurd naar {$count} spelers.");

        try {
            $chatBotService->broadcast(
                '🚨 MEGADEAL LOTERIJ: De trekking is vervroegd naar vanavond 22.00 uur! Alle loten zijn nu 80% goedkoper (€2.000 contant of 3 VIP Sterren). 100% van de inleg stroomt direct naar de megapot!',
                'global'
            );
            $this->info('Chat broadcast geplaatst in plein chat.');
        } catch (Throwable $e) {
            $this->warn('Chat broadcast niet gelukt: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}

