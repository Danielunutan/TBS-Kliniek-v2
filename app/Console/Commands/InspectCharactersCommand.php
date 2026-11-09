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
        $this->info("BOSSIE TRANSACTIES / LOGS VANDAAG:");
        $logs = DB::table('audit_logs')
            ->where('character_id', 66)
            ->where('created_at', '>=', '2026-11-09 16:00:00')
            ->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] {$l->action} - {$l->description}");
        }

        $this->info("\nCHECK CODES GECLAIMD DOOR BOSSIE VANDAAG:");
        if (\Illuminate\Support\Facades\Schema::hasTable('vault_codes')) {
            $codes = DB::table('vault_codes')->where('used_by_character_id', 66)->get();
            foreach ($codes as $c) {
                $this->line("Code: {$c->code} - {$c->reward_type} {$c->reward_amount} op {$c->used_at}");
            }
        }

        $this->info("\nCHECK KOFFER LOGS VANDAAG:");
        $koffers = DB::table('koffer_logs')->where('character_id', 66)->get();
        $this->line("Koffers geopend door Bossie: " . $koffers->count());
        foreach ($koffers as $k) {
            $this->line("Koffer: {$k->tier} - cash €{$k->cash_reward} op {$k->created_at}");
        }

        return self::SUCCESS;
    }
}
