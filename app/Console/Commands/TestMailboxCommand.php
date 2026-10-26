<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PleskMailService;
use Illuminate\Console\Command;

class TestMailboxCommand extends Command
{
    protected $signature = 'mailbox:test {to=vragen@tbs-kliniek.nl}';

    protected $description = 'Send a test email through PleskMailService and inspect IMAP';

    public function handle(PleskMailService $mailService): int
    {
        $to = $this->argument('to');
        $this->info("Sending test email to: {$to}");

        $user = User::where('role', 'owner')->first() ?? User::first();
        if (! $user) {
            $this->error('No user found');

            return Command::FAILURE;
        }

        $sent = $mailService->sendEmail(
            $user,
            $to,
            'Test Bericht - TBS Kliniek Mailbox',
            "Hallo!\n\nDit is een test e-mail ter controle van de mailbox van TBS Kliniek.\n\nGroeten,\nKliniek Team",
            null,
            'daniel'
        );

        if ($sent) {
            $this->info("SUCCESS: Email sent via Brevo to {$to}!");
        } else {
            $this->error("FAILED to send email to {$to}");
        }

        $this->info('Checking IMAP folder stats...');
        $stats = $mailService->getFolderStats();
        $this->table(['Folder', 'Total', 'Unread'], collect($stats)->map(function ($val, $key) {
            return [$key, $val['total'], $val['unread']];
        }));

        $this->info('Listing recent messages...');
        $paged = $mailService->listMessages('INBOX', 1, 10);
        $this->info("Total in INBOX: {$paged['total']}");
        foreach ($paged['messages'] as $m) {
            $this->line("- [#{$m['uid']}] From: {$m['from_email']} | Subject: {$m['subject']} | Date: {$m['date']}");
        }

        return Command::SUCCESS;
    }
}
