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
        $this->info('Controleren van cel upgrades en audit logs in de database...');

        $resetCutoff = '2026-11-09 17:00:00';
        $this->info("Seizoen 2 reset peildatum: {$resetCutoff}");

        $characters = Character::all()->filter(function (Character $c) {
            $upgrades = $c->cell_upgrades;
            return ! empty($upgrades) && is_array($upgrades);
        });

        $this->info("Aantal karakters met actieve cel upgrades: " . $characters->count());

        $resetCount = 0;
        $keptCount = 0;

        foreach ($characters as $char) {
            // Check if this character performed any cell upgrades AFTER the season reset
            $postResetUpgradeLogs = \App\Models\AuditLog::where('action', 'cell.upgrade')
                ->where('character_id', $char->id)
                ->where('created_at', '>=', $resetCutoff)
                ->orderBy('created_at', 'asc')
                ->get();

            $upgradesJson = json_encode($char->cell_upgrades);

            if ($postResetUpgradeLogs->isNotEmpty()) {
                $keptCount++;
                $this->info("   [BEHOUDEN] [#{$char->id}] {$char->name}: {$upgradesJson} (Upgrade aangeschaft na reset op {$postResetUpgradeLogs->last()->created_at})");
            } else {
                $resetCount++;
                $this->warn("   [RESETTEN] [#{$char->id}] {$char->name}: {$upgradesJson} (Oude upgrades van voor de reset)");

                if (! $this->option('dry-run')) {
                    $char->cell_upgrades = null;
                    $char->save();
                }
            }
        }

        $this->newLine();
        if ($this->option('dry-run')) {
            $this->warn("DRY RUN: {$resetCount} karakters zouden worden gereset, {$keptCount} behouden.");
        } else {
            $this->info("KLAAR: {$resetCount} karakters gereset naar 0 cel upgrades, {$keptCount} karakters met upgrades van na de reset behouden!");
        }

        return self::SUCCESS;
    }
}
