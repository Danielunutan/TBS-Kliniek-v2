<?php

namespace App\Console\Commands;

use App\Services\EventCashHuntService;
use Illuminate\Console\Command;

class ResolveCashHuntEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:resolve-cash-hunt {--force : Force resolve even if event has not reached midnight yet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve the Cash Hunt event at midnight and reward the #1 player with 750 stars';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! EventCashHuntService::hasEnded()) {
            $this->warn('Event has not ended yet. Use --force to resolve prematurely.');

            return self::FAILURE;
        }

        $this->info('Resolving De Grote Geldjacht event...');
        $result = EventCashHuntService::resolveWinner((bool) $this->option('force'));

        if (! $result) {
            $this->warn('No earnings found during the event period.');

            return self::SUCCESS;
        }

        $winner = $result['winner'];
        $earned = number_format($result['total_earned'], 0, ',', '.');
        $this->info("Winner awarded! Character {$winner->name} earned €{$earned} and received 750 stars.");

        return self::SUCCESS;
    }
}
