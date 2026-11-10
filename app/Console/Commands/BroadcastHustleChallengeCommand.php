<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\ChatBotService;
use App\Services\EventHustleChallengeService;
use Illuminate\Console\Command;
use Throwable;

class BroadcastHustleChallengeCommand extends Command
{
    protected $signature = 'tbs:broadcast-hustle-challenge';
    protected $description = 'Verstuur notificatie en chat broadcast naar alle spelers over De Grote Hossel Challenge';

    public function handle(ChatBotService $chatBotService): int
    {
        $this->info('Start versturen Hossel Challenge notificaties...');

        $title = '🏆 De Grote Hossel Challenge is Live!';
        $message = 'Strijd nu mee in De Grote Hossel Challenge tot vrijdag 23:59! Voer zware hossels uit onder Hosselen en maak kans op 750 Gouden Sterren ⭐ en €150.000.000 contant bankgeld!';

        $characters = Character::playable()->get();
        $count = 0;

        foreach ($characters as $char) {
            try {
                GameNotification::create([
                    'character_id' => $char->id,
                    'type' => 'event',
                    'icon' => 'fa-trophy',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false,
                ]);
                $count++;
            } catch (Throwable $e) {
                $this->error("Fout bij speler {$char->id}: " . $e->getMessage());
            }
        }

        $this->info("Notificatie verstuurd naar {$count} actieve spelers.");

        try {
            $chatBotService->broadcast(
                '🏆 EVENT ALERT: De Grote Hossel Challenge is in volle gang! Voer zware klussen uit onder Hosselen tot vrijdag 23:59:59. De #1 wint 750 Gouden Sterren ⭐ en €150.000.000 contant bankgeld!',
                'global'
            );
            $this->info('Chat broadcast succesvol geplaatst in plein chat.');
        } catch (Throwable $e) {
            $this->warn('Chat broadcast niet gelukt: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
