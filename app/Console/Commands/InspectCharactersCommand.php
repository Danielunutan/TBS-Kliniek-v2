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
        $this->info("DENNIS (#86):");
        $dennis = Character::find(86);
        if ($dennis) {
            $this->line("Level: {$dennis->level}, Respect: {$dennis->respect}, Cash: {$dennis->cash}, Bank: {$dennis->bank}, Gang: {$dennis->gang_id}");
        }

        $this->info("\nBOSSIE (#66):");
        $bossie = Character::find(66);
        if ($bossie) {
            $this->line("Level: {$bossie->level}, Respect: {$bossie->respect}, Cash: {$bossie->cash}, Bank: {$bossie->bank}, Gang: {$bossie->gang_id}");
        }

        $this->info("\nBOSSIE AUDIT LOGS RECENT:");
        $logs = DB::table('audit_logs')
            ->where('character_id', 66)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] Action: {$l->action} | Details: {$l->details}");
        }

        $this->info("\nDENNIS AUDIT LOGS RECENT:");
        $dlogs = DB::table('audit_logs')
            ->where('character_id', 86)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        foreach ($dlogs as $l) {
            $this->line("[{$l->created_at}] Action: {$l->action} | Details: {$l->details}");
        }

        $this->info("\nALLE BENDES OVERZICHT:");
        $gangs = Gang::all();
        foreach ($gangs as $g) {
            $this->line("#{$g->id} {$g->name} - Kas: {$g->treasury}, Prot: {$g->protected_treasury}, Resp: {$g->respect}, Chat: {$g->has_gang_chat}, Maskers: {$g->masks}");
        }

        return self::SUCCESS;
    }
}
