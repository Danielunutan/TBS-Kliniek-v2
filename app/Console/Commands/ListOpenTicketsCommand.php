<?php

namespace App\Console\Commands;

use App\Models\SupportTicket;
use Illuminate\Console\Command;

class ListOpenTicketsCommand extends Command
{
    protected $signature = 'ticket:list-open';

    protected $description = 'List all open support tickets';

    public function handle(): int
    {
        $tickets = SupportTicket::whereIn('status', ['open', 'in_behandeling'])
            ->with(['character', 'messages'])
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('Geen openstaande tickets gevonden.');

            return self::SUCCESS;
        }

        $this->info("Gevonden openstaande tickets: {$tickets->count()}");
        foreach ($tickets as $t) {
            $this->line('--------------------------------------------------');
            $this->info("ID: {$t->id} | Nummer: {$t->ticket_number} | Status: {$t->status} | Categorie: {$t->category}");
            $this->line('Speler: '.($t->character?->name ?? 'Onbekend')." (User ID: {$t->user_id})");
            $this->line("Onderwerp: {$t->subject}");
            $firstMessage = $t->messages->first();
            $this->line("Eerste bericht:\n".($firstMessage?->message ?? 'Geen bericht'));
            $lastMessage = $t->messages->last();
            if ($lastMessage && $lastMessage->id !== $firstMessage?->id) {
                $this->line('Laatste bericht ('.($lastMessage->is_staff ? 'Staff' : 'Speler')."):\n".$lastMessage->message);
            }
        }
        $this->line('--------------------------------------------------');

        return self::SUCCESS;
    }
}
