<?php

namespace App\Console\Commands;

use App\Models\LotteryRound;
use Illuminate\Console\Command;

class DrawLotteryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lottery:draw {--force : Force the draw even if draws_at is in the future}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and execute the lottery draw if drawing time has passed (or forced)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $round = LotteryRound::where('is_drawn', false)->orderByDesc('id')->first();

        if (! $round) {
            $this->info('Geen actieve loterijronde gevonden. Er wordt een nieuwe ronde aangemaakt...');
            LotteryRound::current();

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');

        if ($force || ($round->draws_at && now()->gte($round->draws_at))) {
            $this->info("Loterijronde #{$round->round_number} wordt nu getrokken...");
            $nextRound = $round->executeDraw();
            $this->info('Trekking voltooid! Winnaar is uitbetaald (30% bank, 70% contant) en nieuwsbericht is gepubliceerd.');
            $this->info("Nieuwe actieve ronde: #{$nextRound->round_number} met jackpot €".number_format($nextRound->jackpot, 0, ',', '.'));

            return self::SUCCESS;
        }

        $this->line("Ronde #{$round->round_number} is nog niet verlopen (Trekking gepland op: {$round->draws_at}).");

        return self::SUCCESS;
    }
}
