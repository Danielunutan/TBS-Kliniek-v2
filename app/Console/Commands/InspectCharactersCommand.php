<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Character;
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
        $this->info("ALLE TRANSACTIES VANDAAG TUSSEN 17:00 EN 17:36:");
        $logs = DB::table('audit_logs')
            ->where('created_at', '>=', '2026-11-09 17:00:00')
            ->orderBy('id', 'asc')
            ->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] [Char {$l->character_id}] {$l->action} - {$l->description}");
        }

        return self::SUCCESS;
    }
}
