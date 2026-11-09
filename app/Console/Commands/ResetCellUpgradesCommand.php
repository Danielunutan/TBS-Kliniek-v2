<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetCellUpgradesCommand extends Command
{
    protected $signature = 'cell:reset-upgrades {--dry-run : Alleen tellen en tonen}';
    protected $description = 'Reset alle cel upgrades van alle karakters naar niveau 0';

    public function handle(): int
    {
        $this->info('Controleren van cel upgrades in de database...');

        $characters = Character::all()->filter(function (Character $c) {
            $upgrades = $c->cell_upgrades;
            return ! empty($upgrades) && is_array($upgrades);
        });

        $this->info("Aantal karakters met actieve cel upgrades: " . $characters->count());

        foreach ($characters as $char) {
            $upgradesJson = json_encode($char->cell_upgrades);
            $this->line("   -> [#{$char->id}] {$char->name}: {$upgradesJson}");
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: Geen wijzigingen opgeslagen.');
            return self::SUCCESS;
        }

        // Reset via Eloquent and direct DB update to be 100% thorough
        $affected = DB::table('characters')->update(['cell_upgrades' => null]);

        $this->info("Succesvol alle cel upgrades gereset ({$affected} rijen bijgewerkt).");

        return self::SUCCESS;
    }
}
