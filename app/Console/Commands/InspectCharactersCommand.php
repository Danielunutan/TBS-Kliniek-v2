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
        $this->info("KARAKTERS MET CASH > 250 OF BANK > 1000 OF RESPECT > 10:");
        $chars = Character::where('cash', '>', 250)
            ->orWhere('bank', '>', 1000)
            ->orWhere('respect', '>', 10)
            ->orWhere('level', '>', 1)
            ->get();
        $this->line("Aantal gevonden: " . $chars->count());
        foreach ($chars as $c) {
            $this->line("#{$c->id} {$c->name} - Level: {$c->level}, Respect: {$c->respect}, Cash: €" . number_format($c->cash, 0, ',', '.') . ", Bank: €" . number_format($c->bank, 0, ',', '.'));
        }

        $this->info("\nBENDES MET KAS > 0 OF RESPECT > 0:");
        $gangs = Gang::where('treasury', '>', 0)->orWhere('respect', '>', 0)->orWhere('protected_treasury', '>', 0)->get();
        $this->line("Aantal bende gevonden: " . $gangs->count());
        foreach ($gangs as $g) {
            $this->line("#{$g->id} {$g->name} - Kas: €{$g->treasury}, Respect: {$g->respect}, Chat: {$g->has_gang_chat}");
        }

        return self::SUCCESS;
    }
}
