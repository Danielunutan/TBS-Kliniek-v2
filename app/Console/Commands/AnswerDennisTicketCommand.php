<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\GameNotification;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tbs:answer-dennis-ticket')]
#[Description('Beantwoord ticket TBS-II2P-127 van Dennis over boot reviseren')]
class AnswerDennisTicketCommand extends Command
{
    public function handle(): int
    {
        $ticket = SupportTicket::where('ticket_number', 'like', '%127%')
            ->orWhere('subject', 'like', '%boot%')
            ->first();

        if (! $ticket) {
            $this->error('Ticket TBS-II2P-127 niet gevonden.');
            return self::FAILURE;
        }

        $adminUser = User::whereIn('email', ['admin@tbskliniek.nl', 'danielunutan@icloud.com'])->first();
        $adminChar = Character::where('id', 1)->first() ?? Character::first();

        $replyMessage = "Hoi Dennis, super scherp gezien en heel erg bedankt voor het meedenken! We hebben dit direct voor je doorgevoerd in het heistsysteem. Als jullie bende over de vereiste boot (of een ander vereist type) beschikt maar deze aan onderhoud toe is, selecteert het spel nu direct jullie eigen boot en staat er keurig: 'Boot Toe aan Revisie in Bende Garage'. Hierdoor weet je meteen precies wat er aan de hand is en brengt de knop je direct naar de revisiepagina. Heel veel succes met de bende heists!";

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $adminUser?->id,
            'character_id' => $adminChar?->id,
            'message' => $replyMessage,
            'is_staff' => true,
        ]);

        $ticket->update([
            'status' => 'beantwoord',
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
        ]);

        if ($ticket->character_id) {
            GameNotification::create([
                'character_id' => $ticket->character_id,
                'type' => 'ticket',
                'icon' => 'fa-headset',
                'title' => 'Reactie op Support Ticket',
                'message' => "Je ticket '{$ticket->subject}' (#{$ticket->ticket_number}) is beantwoord door de directie.",
            ]);
        }

        $this->info("Ticket #{$ticket->ticket_number} succesvol beantwoord en bijgewerkt naar 'beantwoord'!");

        return self::SUCCESS;
    }
}
