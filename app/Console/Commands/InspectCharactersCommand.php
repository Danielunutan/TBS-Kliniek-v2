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
        $this->info("BOSSIE DATA:");
        $bossie = Character::find(66);
        $this->line(json_encode($bossie?->toArray(), JSON_PRETTY_PRINT));

        $this->info("CHECK ALL TRANSACTIONS OF BOSSIE IN ANY TABLE:");
        $tables = ['bank_transactions', 'fights', 'attacks', 'character_missions', 'character_heists'];
        foreach ($tables as $t) {
            $count = DB::table($t)->where('character_id', 66)->count();
            $this->line("Table {$t}: {$count} records");
        }

        return self::SUCCESS;
    }
}
