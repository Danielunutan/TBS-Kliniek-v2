<?php

namespace App\Console\Commands;

use App\Services\EveningVaultDropService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('vault:evening-drop {--force : Forceer toekenning ongeacht het tijdstip}')]
#[Description('Keert 3 gratis kluiscodes uit als dagelijkse inlog bonus aan alle spelers die online zijn tussen 22:00 en 22:30')]
class EveningVaultDropCommand extends Command
{
    public function handle(EveningVaultDropService $dropService): int
    {
        $force = (bool) $this->option('force');

        if (! $force && ! $dropService->isDropWindowActive()) {
            $this->warn('Het is momenteel buiten het 22:00 - 22:30 inlog bonus venster. Gebruik --force om geforceerd te testen.');

            return self::FAILURE;
        }

        $this->info('Inlog bonus 22:00 - 22:30: Bezig met toekennen van 3 gratis kluiscodes aan online patiënten...');

        $count = $dropService->awardAllOnline();

        $this->info("Klaar! {$count} online patiënten hebben 3 gratis kluiscodes ontvangen in hun postvak en kluis.");

        return self::SUCCESS;
    }
}
