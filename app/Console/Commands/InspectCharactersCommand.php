<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Character;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tbs:inspect-respect')]
#[Description('Inspect high respect characters and gangs')]
class InspectCharactersCommand extends Command
{
    public function handle(): int
    {
        $this->info("BOSSIE AUDIT LOGS (ID 66):");
        $logs = AuditLog::where('character_id', 66)->latest()->take(25)->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] {$l->action} - {$l->description}");
        }

        $bossie = Character::find(66);
        $this->line("Bossie nu: Cash €{$bossie->cash}, Bank €{$bossie->bank}, Respect {$bossie->respect}, Level {$bossie->level}");

        return self::SUCCESS;
    }
}
