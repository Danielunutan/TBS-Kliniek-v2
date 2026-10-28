<?php

namespace App\Console\Commands;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Console\Command;

class AnswerTicketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:answer {ticket_number} {--message=} {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send official staff reply to a support ticket';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ticketNumber = $this->argument('ticket_number');
        $message = $this->option('message');
        $filePath = $this->option('file');

        if ($filePath && file_exists($filePath)) {
            $message = file_get_contents($filePath);
        }

        if (! $message) {
            $this->error('Geen bericht opgegeven (--message of --file).');

            return Command::FAILURE;
        }

        $ticket = SupportTicket::where('ticket_number', $ticketNumber)->first();

        if (! $ticket) {
            $this->error("Ticket {$ticketNumber} niet gevonden.");

            return Command::FAILURE;
        }

        $adminUser = User::where('role', 'owner')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::where('is_staff', true)->first();

        if (! $adminUser) {
            $this->error('Geen admin/staff account gevonden.');

            return Command::FAILURE;
        }

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $adminUser->id,
            'character_id' => $adminUser->character?->id,
            'is_staff' => true,
            'message' => $message,
        ]);

        $ticket->update([
            'status' => 'beantwoord',
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
        ]);

        $this->info("Ticket {$ticketNumber} succesvol beantwoord namens {$adminUser->name}!");

        return Command::SUCCESS;
    }
}
