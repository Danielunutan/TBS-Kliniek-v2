<?php

namespace App\Console\Commands;

use App\Services\EventVaultCrackingService;
use Illuminate\Console\Command;

class ResolveVaultChallengeEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:resolve-vault-challenge {--force : Force resolve even if challenge has not reached deadline yet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve the Vault Code Cracking challenge at Wednesday 23:59 and reward the #1 player with 2,500 stars and 100 vault codes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! EventVaultCrackingService::hasEnded()) {
            $this->warn('Challenge has not ended yet. Deadline is Wednesday 23:59:59. Use --force to resolve prematurely.');

            return self::FAILURE;
        }

        $this->info('Resolving De Grote Kluiscode Challenge event...');
        $result = EventVaultCrackingService::resolveWinner((bool) $this->option('force'));

        if (! $result) {
            $this->warn('No vault code cracks found or challenge already resolved.');

            return self::SUCCESS;
        }

        $winner = $result['winner'];
        $cracked = number_format($result['total_cracked'], 0, ',', '.');
        $this->info("Winner awarded! Character {$winner->name} cracked {$cracked} vault codes and received 2.500 stars and 100 vault codes.");

        return self::SUCCESS;
    }
}
