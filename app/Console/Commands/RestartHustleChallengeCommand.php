<?php

namespace App\Console\Commands;

use App\Services\EventHustleChallengeService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('tbs:restart-hustle-challenge {--force : Voer direct uit zonder bevestiging} {--dry-run : Simuleer de herstart zonder wijzigingen op te slaan}')]
#[Description('Herstart De Grote Hossel Challenge vanaf 0 met looptijd tot en met vrijdag 23:59:59')]
class RestartHustleChallengeCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================================');
        $this->info('TBS KLINIEK - HERSTART DE GROTE HOSSEL CHALLENGE');
        $this->info('========================================================');

        $isDryRun = (bool) $this->option('dry-run');

        if (! $isDryRun && ! $this->option('force')) {
            if (! $this->confirm('Weet je zeker dat je De Grote Hossel Challenge wilt herstarten en alle bestaande inzendingen wilt wissen?')) {
                $this->warn('Herstart geannuleerd.');

                return self::FAILURE;
            }
        }

        DB::beginTransaction();

        try {
            if (Schema::hasTable('event_hustle_entries')) {
                $before = DB::table('event_hustle_entries')->count();
                if (! $isDryRun) {
                    DB::table('event_hustle_entries')->delete();
                }
                $this->info("   -> Tabel [event_hustle_entries] geleegd ({$before} oude beurten verwijderd, teller op 0).");
            }

            // Artikel synchroniseren
            if (! $isDryRun) {
                $article = EventHustleChallengeService::syncChallengeArticle();
                if ($article) {
                    $this->info("   -> Challenge artikel bijgewerkt en gepubliceerd: '{$article->title}' (ID: {$article->id})");
                }
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->warn('DRY RUN: Wijzigingen zijn teruggedraaid.');
            } else {
                DB::commit();
                $this->info('========================================================');
                $this->info('DE GROTE HOSSEL CHALLENGE IS SUCCESVOL HERSTART!');
                $this->info('Looptijd: Van nu tot en met vrijdag 13 november 23:59:59');
                $this->info('Prijzenpot: 750 Gouden Sterren ⭐ + €150.000.000 contant bankgeld');
                $this->info('========================================================');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Fout tijdens herstart: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
