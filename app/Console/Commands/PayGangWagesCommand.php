<?php

namespace App\Console\Commands;

use App\Models\GangNpc;
use App\Services\GangWageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gang:pay-wages')]
#[Description('Betaalt automatisch het opgebouwde uurloon uit aan alle actieve bendeleden')]
class PayGangWagesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GangWageService $wageService): int
    {
        GangNpc::purgeAllExpired();

        $this->info('Controleren en uitbetalen van bende-uurlonen...');

        $totalPaid = $wageService->payAllMembers();

        $this->info('Uitbetaling voltooid! Totaal uitbetaald: €'.number_format($totalPaid, 0, ',', '.'));

        return self::SUCCESS;
    }
}
