<?php

namespace App\Console\Commands;

use App\Services\DailyBattleService;
use Illuminate\Console\Command;

class SettleDailyBattleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battle:settle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Settle past 24-hour daily battles and distribute prizes to top 3 winners.';

    /**
     * Execute the console command.
     */
    public function handle(DailyBattleService $battleService): int
    {
        $this->info('Controleren op onafgeronde 24 Uur Battles...');
        $battleService->checkAndSettle();
        $this->info('24 Uur Battles succesvol afgehandeld en prijzen toegekend!');

        return Command::SUCCESS;
    }
}
