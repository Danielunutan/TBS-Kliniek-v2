<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\Gang;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('tbs:inspect-respect')]
#[Description('Inspect high respect characters and gangs')]
class InspectCharactersCommand extends Command
{
    public function handle(): int
    {
        $this->info("BOSSIE AUDIT LOGS TODAY:");
        $logs = DB::table('audit_logs')
            ->where('character_id', 66)
            ->where('created_at', '>=', '2026-11-09 00:00:00')
            ->orderBy('id', 'asc')
            ->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] {$l->action}: {$l->details}");
        }

        $this->info("\nDENNIS AUDIT LOGS TODAY:");
        $dlogs = DB::table('audit_logs')
            ->where('character_id', 86)
            ->where('created_at', '>=', '2026-11-09 17:00:00')
            ->orderBy('id', 'asc')
            ->get();
        foreach ($dlogs as $l) {
            $this->line("[{$l->created_at}] {$l->action}: {$l->details}");
        }

        return self::SUCCESS;
    }
}
