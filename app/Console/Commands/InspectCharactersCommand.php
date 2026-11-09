<?php

namespace App\Console\Commands;

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
        $this->info("BOSSIE CASH/BANK CHANGES:");
        $logs = DB::table('audit_logs')
            ->where('character_id', 66)
            ->where(function($q) {
                $q->where('details', 'like', '%cash%')
                  ->orWhere('details', 'like', '%bank%')
                  ->orWhere('details', 'like', '%5000000%')
                  ->orWhere('action', 'like', '%gang%')
                  ->orWhere('action', 'like', '%bank%');
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] {$l->action}: {$l->details}");
        }

        $this->info("\nDENNIS THEFTS ON BOSSIE:");
        $thefts = DB::table('audit_logs')
            ->where('character_id', 86)
            ->where('action', 'theft')
            ->orderBy('id', 'asc')
            ->get();
        $totalStolen = 0;
        foreach ($thefts as $t) {
            $data = json_decode($t->details, true);
            $stolen = $data['cash_stolen'] ?? 0;
            $totalStolen += $stolen;
            $this->line("[{$t->created_at}] Stolen: €" . number_format($stolen, 0, ',', '.') . " (Total so far: €" . number_format($totalStolen, 0, ',', '.') . ")");
        }

        return self::SUCCESS;
    }
}
