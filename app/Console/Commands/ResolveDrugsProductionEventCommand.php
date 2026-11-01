<?php

namespace App\Console\Commands;

use App\Services\EventDrugsProductionService;
use Illuminate\Console\Command;

class ResolveDrugsProductionEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:resolve-drugs-production {--force : Force resolve even if event has not reached deadline yet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve the Drugs Production event at Sunday 23:59 and reward the #1 player with 5,000,000 cash and 1,000 stars';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! EventDrugsProductionService::hasEnded()) {
            $this->warn('Event has not ended yet. Deadline is Sunday 23:59:59. Use --force to resolve prematurely.');

            return self::FAILURE;
        }

        $this->info('Resolving De Grote Drugsproductie event...');
        $result = EventDrugsProductionService::resolveWinner((bool) $this->option('force'));
        EventDrugsProductionService::syncWinnerArticle();

        if (! $result) {
            $this->warn('No production found or event already resolved.');

            return self::SUCCESS;
        }

        $winner = $result['winner'];
        $produced = number_format($result['total_produced'], 0, ',', '.');
        $this->info("Winner awarded! Character {$winner->name} produced {$produced} units and received €5.000.000 cash and 1.000 stars.");

        return self::SUCCESS;
    }
}
