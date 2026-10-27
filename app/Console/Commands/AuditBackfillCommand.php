<?php

namespace App\Console\Commands;

use App\Services\AuditLogService;
use Illuminate\Console\Command;

class AuditBackfillCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:backfill {--limit=1500 : Max records per source to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill historical gameplay, economy, and moderation events into the audit log with automatic risk assessment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $this->info("Starten met importeren en analyseren van historische logs (limiet: {$limit} per bron)...");

        $stats = AuditLogService::backfillHistory($limit);

        $this->info('Synchronisatie voltooid!');
        $this->line("- Totaal verwerkt: {$stats['total_synced']}");
        $this->line("- Hoog risico events: {$stats['high_risk']}");
        $this->line("- Verdachte events: {$stats['suspicious']}");

        return Command::SUCCESS;
    }
}
