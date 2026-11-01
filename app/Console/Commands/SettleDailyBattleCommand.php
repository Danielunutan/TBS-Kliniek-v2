<?php

namespace App\Console\Commands;

use App\Services\DailyBattleService;
use Illuminate\Console\Command;

class SettleDailyBattleCommand extends Command
{
    /**
     * Define the name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battle:settle';

    /**
     * Describe the console command purpose.
     *
     * @var string
     */
    protected $description = 'Settle completed 24-hour escalation battles and grant prizes to the top 3 players.';

    /**
     * Execute the console command.
     */
    public function handle(DailyBattleService $battleService): int
    {
        $this->info('Openstaande Etmaal-Escalaties worden gecontroleerd...');
        $battleService->checkAndSettle();
        $this->info('Alle afgeronde Etmaal-Escalaties zijn verwerkt en de buit is uitgekeerd!');

        return Command::SUCCESS;
    }
}
