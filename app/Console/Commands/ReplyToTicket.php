<?php

namespace App\Console\Commands;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Console\Command;

class ReplyToTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:reply
                            {ticket : Ticket number (e.g. TBS-R4G-711)}
                            {--message= : Direct reply message (skips prompt)}
                            {--admin-id= : Admin user ID that sends the reply}
                            {--status=beantwoord : New ticket status (open/in_behandeling/beantwoord/gesloten)}
                            {--force : Do not ask for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reply to a support ticket as staff from the command line';

    public function handle(): int
    {
        $ticketNumber = $this->argument('ticket');
        $newStatus = $this->option('status');
        $adminId = $this->option('admin-id');
        $messageOption = $this->option('message');

        $ticket = SupportTicket::where('ticket_number', $ticketNumber)->first();

        if (! $ticket) {
            $this->error("Ticket {$ticketNumber} not found.");

            return 1;
        }

        // Resolve admin user
        $adminUser = $adminId
            ? User::find($adminId)
            : (User::where('role', 'owner')->first() ?? User::where('role', 'admin')->first() ?? User::where('is_staff', true)->first());

        if (! $adminUser) {
            $this->error('No admin user found. Use --admin-id to specify one.');

            return 1;
        }

        $this->info("Ticket: [{$ticket->ticket_number}] {$ticket->subject}");
        $this->info("Status: {$ticket->status} → {$newStatus}");
        $this->info("Staff: {$adminUser->name} (ID: {$adminUser->id})");

        $message = $messageOption ?? $this->ask('Enter your reply message');

        if (empty($message)) {
            $this->error('Message cannot be empty.');

            return 1;
        }

        if (! $this->option('force') && ! $messageOption && ! $this->confirm("Send this reply and set status to '{$newStatus}'?", true)) {
            $this->warn('Cancelled.');

            return 0;
        }

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $adminUser->id,
            'character_id' => $adminUser->character?->id,
            'is_staff' => true,
            'message' => $message,
        ]);

        $ticket->update([
            'status' => $newStatus,
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
            'closed_at' => $newStatus === 'gesloten' ? now() : null,
        ]);

        $this->info("✓ Reply sent and ticket status updated to '{$newStatus}'.");

        return 0;
    }
}
