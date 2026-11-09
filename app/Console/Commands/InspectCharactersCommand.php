<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Gang;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tbs:inspect-respect')]
#[Description('Inspect high respect characters and gangs')]
class InspectCharactersCommand extends Command
{
    public function handle(): int
    {
        $dennis = Character::find(86);
        $this->info("DENNIS (#86): Level {$dennis->level}, Respect {$dennis->respect}, Cash €{$dennis->cash}, Bank €{$dennis->bank}");

        $bossie = Character::find(66);
        $this->info("BOSSIE (#66): Level {$bossie->level}, Respect {$bossie->respect}, Cash €{$bossie->cash}, Bank €{$bossie->bank}");

        $chars = Character::where('cash', '>', 250)
            ->orWhere('bank', '>', 1000)
            ->orWhere('respect', '>', 10)
            ->orWhere('level', '>', 1)
            ->get();
        $this->info("AFWIJKENDE KARAKTERS GEVONDEN: " . $chars->count());
        foreach ($chars as $c) {
            $this->line("#{$c->id} {$c->name}: Level {$c->level}, Resp {$c->respect}, Cash €{$c->cash}, Bank €{$c->bank}");
        }

        $gangs = Gang::where('treasury', '>', 0)
            ->orWhere('protected_treasury', '>', 0)
            ->orWhere('respect', '>', 0)
            ->orWhere('masks', '>', 0)
            ->get();
        $this->info("BENDES MET KAS/RESP/MASKERS > 0: " . $gangs->count());
        foreach ($gangs as $g) {
            $this->line("#{$g->id} {$g->name}: Kas €{$g->treasury}, Resp {$g->respect}, Chat {$g->has_gang_chat}");
        }

        $allGangs = Gang::all();
        $this->info("TOTAAL BENDES STATUS:");
        foreach ($allGangs as $g) {
            $this->line("#{$g->id} {$g->name} [{$g->tag}]: Kas €{$g->treasury}, Prot €{$g->protected_treasury}, Resp {$g->respect}, Chat {$g->has_gang_chat}");
        }

        return self::SUCCESS;
    }
}
