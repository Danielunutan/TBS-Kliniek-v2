<?php

namespace App\Console\Commands;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\GameSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbs:maintenance {action=on : Action to perform: on, off, status} {--time= : Optional expected return time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle TBS Kliniek maintenance mode with access for administrators';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = strtolower((string) $this->argument('action'));
        $lockFile = CheckMaintenanceMode::lockFilePath();

        if ($action === 'off') {
            if (file_exists($lockFile)) {
                unlink($lockFile);
            }
            try {
                if (Schema::hasTable('game_settings')) {
                    GameSetting::set('maintenance_mode_enabled', '0', 'Onderhoudsmodus status');
                }
            } catch (\Throwable) {
                // Ignore database errors during CLI toggle.
            }
            $this->info('TBS Kliniek onderhoudsmodus is UITGESCHAKELD. De deuren zijn weer open voor alle spelers!');

            return Command::SUCCESS;
        }

        if ($action === 'status') {
            if (CheckMaintenanceMode::isEnabled()) {
                $this->warn('Onderhoudsmodus is momenteel ACTIEF (alleen beheerders hebben toegang).');
                $data = CheckMaintenanceMode::getMetadata();
                if (! empty($data['target_time'])) {
                    $this->line('Verwachte eindtijd: '.$data['target_time']);
                }
            } else {
                $this->info('Onderhoudsmodus is momenteel UIT (Iedereen heeft toegang).');
            }

            return Command::SUCCESS;
        }

        // Action is ON
        $targetTime = $this->option('time') ? (string) $this->option('time') : null;
        $payload = [
            'enabled' => true,
            'reason' => 'Volledige en eerlijke reset naar aanleiding van spelersfeedback',
            'target_time' => $targetTime,
            'activated_at' => now()->toIso8601String(),
        ];

        if (! is_dir(dirname($lockFile))) {
            mkdir(dirname($lockFile), 0755, true);
        }

        file_put_contents($lockFile, json_encode($payload, JSON_PRETTY_PRINT), LOCK_EX);

        try {
            if (Schema::hasTable('game_settings')) {
                GameSetting::set('maintenance_mode_enabled', '1', 'Onderhoudsmodus status');
                GameSetting::set('maintenance_mode_metadata', $payload, 'Onderhoudsmodus metadata');
            }
        } catch (\Throwable) {
            // Ignore database errors during CLI toggle.
        }

        $this->info('TBS Kliniek is nu in ONDERHOUDSMODUS geplaatst!');
        $this->line('Beheerders hebben na inloggen toegang.');

        return Command::SUCCESS;
    }
}
