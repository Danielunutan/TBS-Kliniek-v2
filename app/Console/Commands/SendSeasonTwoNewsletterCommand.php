<?php

namespace App\Console\Commands;

use App\Mail\SeasonTwoLaunchMail;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

#[Signature('tbs:send-season-two-newsletter {--dry-run : Toon alleen de ontvangers zonder daadwerkelijk te versturen} {--test-email= : Stuur alleen een testmail naar dit specifieke adres} {--limit= : Beperk het aantal e-mails}')]
#[Description('Verstuur de officiële Seizoen 2 Sprint nieuwsbrief via Brevo naar alle geregistreerde spelers')]
class SendSeasonTwoNewsletterCommand extends Command
{
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('TBS KLINIEK - SEIZOEN 2 SPRINT BREVO NIEUWSBRIEF');
        $this->info('====================================================');

        $isDryRun = (bool) $this->option('dry-run');
        $testEmail = $this->option('test-email');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if ($testEmail) {
            $this->info("Versturen van test e-mail naar: {$testEmail}...");
            $dummyUser = User::where('email', $testEmail)->first() ?? new User([
                'name' => 'Test Speler',
                'email' => $testEmail,
            ]);

            try {
                Mail::to($testEmail)->send(new SeasonTwoLaunchMail($dummyUser));
                $this->info("Test e-mail succesvol verzonden via Brevo naar {$testEmail}!");
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("Fout bij test e-mail verzenden: " . $e->getMessage());
                return self::FAILURE;
            }
        }

        $query = User::query()
            ->where(function ($q) {
                $q->whereNull('newsletter_subscribed')
                  ->orWhere('newsletter_subscribed', true);
            })
            ->whereNotNull('email')
            ->where('email', 'like', '%@%')
            ->where('is_banned', false)
            ->whereNotIn('email', ['1231231@spamok.nl']); // filter bekende throwaway testadressen

        if ($limit) {
            $query->limit($limit);
        }

        $users = $query->orderBy('id', 'asc')->get();
        $totalCount = $users->count();

        $this->info("Aantal geschikte ontvangers gevonden: {$totalCount}");

        if ($isDryRun) {
            $this->warn('DRY RUN: Er worden geen e-mails verzonden.');
            foreach ($users->take(20) as $u) {
                $this->line("   -> [#{$u->id}] {$u->name} <{$u->email}>");
            }
            if ($totalCount > 20) {
                $this->line("   ... en nog " . ($totalCount - 20) . " andere spelers.");
            }
            return self::SUCCESS;
        }

        if ($totalCount === 0) {
            $this->warn('Geen ontvangers gevonden.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new SeasonTwoLaunchMail($user));
                $successCount++;
                // Pauze van 120ms om de Brevo SMTP verbinding soepel te houden
                usleep(120000);
            } catch (\Throwable $e) {
                $failCount++;
                Log::error("Fout bij versturen nieuwsbrief naar {$user->email}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("====================================================");
        $this->info("VERZENDING VOLTOOID VIA BREVO!");
        $this->info("Succesvol verzonden: {$successCount}");
        if ($failCount > 0) {
            $this->warn("Mislukt: {$failCount}");
        }
        $this->info("====================================================");

        return self::SUCCESS;
    }
}
