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
        $this->info('DENNIS DETAIL INSPECTIE (ID 86):');
        $c = Character::find(86);
        if ($c) {
            $this->line("Dennis: Level {$c->level}, XP {$c->xp}, Respect {$c->respect}, Cash {$c->cash}, Bank {$c->bank}, Bullets {$c->bullets}");
        }

        $this->info("\nAUDIT LOGS VOOR DENNIS (LAATSTE 20):");
        $logs = AuditLog::where('character_id', 86)->latest()->take(20)->get();
        foreach ($logs as $l) {
            $this->line("[{$l->created_at}] {$l->action} - {$l->description}");
        }

        $this->info("\nGANG VEHICLES VOOR TOKKIE KARTEL (GANG 6):");
        $vehicles = DB::table('character_vehicles')->where('character_id', 86)->orWhere('gang_id', 6)->get();
        $this->line("Aantal wagens gevonden: " . $vehicles->count());
        foreach ($vehicles as $v) {
            $this->line("Vehicle ID: {$v->id}, vehicle_id: {$v->vehicle_id}, gang_id: {$v->gang_id}, is_gang_owned: {$v->is_gang_owned}");
        }

        $this->info("\nGANG DONATIONS VOOR GANG 6:");
        $donations = DB::table('gang_donations')->where('gang_id', 6)->get();
        foreach ($donations as $d) {
            $this->line("Donatie: {$d->type} - {$d->amount} door char {$d->character_id} op {$d->created_at}");
        }

        return self::SUCCESS;
    }
}
