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
        $this->info('TOP 10 KARAKTERS OP RESPECT:');
        $top = Character::orderByDesc('respect')->take(10)->get();
        foreach ($top as $c) {
            $this->line("#{$c->id} {$c->name} - Respect: {$c->respect} (Vandaag: {$c->today_respect}) - Level: {$c->level} - Gang: {$c->gang?->name} (#{$c->gang_id})");
        }

        $this->info("\nDENNIS ZOEKEN:");
        $dennisChars = Character::where('name', 'like', '%dennis%')->orWhere('name', 'like', '%Dennis%')->get();
        foreach ($dennisChars as $c) {
            $this->line("#{$c->id} {$c->name} - Respect: {$c->respect} - Level: {$c->level} - Gang: {$c->gang?->name} (#{$c->gang_id})");
        }

        $this->info("\nTOP 5 BENDES OP RESPECT & KAS:");
        $gangs = Gang::orderByDesc('respect')->take(5)->get();
        foreach ($gangs as $g) {
            $this->line("#{$g->id} {$g->name} - Respect: {$g->respect} - Kas: €" . number_format($g->treasury, 0, ',', '.') . " - Leden: {$g->members()->count()}");
        }

        return self::SUCCESS;
    }
}
