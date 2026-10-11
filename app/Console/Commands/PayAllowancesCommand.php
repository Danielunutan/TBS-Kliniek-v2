<?php

namespace App\Console\Commands;

use App\Services\AllowanceService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('patients:pay-allowances')]
#[Description('Betaalt automatisch de kwartier-uitkering (€1.000 per 15 min) uit aan alle actieve patiënten zonder bende')]
class PayAllowancesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AllowanceService $allowanceService): int
    {
        $this->info('Controleren en uitbetalen van TBS-patiënten uitkeringen (€1.000 per kwartier)...');

        $totalPaid = $allowanceService->payAllNonGangMembers();

        $this->info('Uitbetaling voltooid! Totaal uitgekeerd: €'.number_format($totalPaid, 0, ',', '.'));

        return self::SUCCESS;
    }
}
