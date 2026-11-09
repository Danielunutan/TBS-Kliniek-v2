<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetCellUpgradesCommand extends Command
{
    protected $signature = 'cell:reset-upgrades {--dry-run : Alleen tellen en tonen}';
    protected $description = 'Reset alle cel upgrades van alle karakters naar niveau 0';

    public function handle(): int
    {
        $this->info('Controleren van cel upgrades en audit logs in de database...');

        $charIds = [42, 52, 86, 113];
        $logs = \App\Models\AuditLog::where('action', 'cell.upgrade')
            ->whereIn('character_id', $charIds)
            ->orderBy('created_at', 'asc')
            ->get();

        $this->info("Aantal cell.upgrade audit logs gevonden voor deze karakters: " . $logs->count());
        foreach ($logs as $log) {
            $this->line("   [{$log->created_at}] Karakter #{$log->character_id} ({$log->character?->name}): {$log->description}");
        }

        // Check season reset time from character or user or audit log
        $resetLog = \App\Models\AuditLog::where('action', 'like', '%reset%')->first();
        if ($resetLog) {
            $this->info("Reset log gevonden op: {$resetLog->created_at} | {$resetLog->action}");
        }

        return self::SUCCESS;
    }
}
