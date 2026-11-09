<?php

use App\Models\GameSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $payload = [
            'enabled' => true,
            'reason' => 'Volledige en eerlijke reset naar aanleiding van spelersfeedback',
            'target_time' => null,
            'activated_at' => now()->toIso8601String(),
        ];

        $lockFile = storage_path('framework/tbs_maintenance.json');
        if (! is_dir(dirname($lockFile))) {
            mkdir(dirname($lockFile), 0755, true);
        }
        file_put_contents($lockFile, json_encode($payload, JSON_PRETTY_PRINT), LOCK_EX);

        if (Schema::hasTable('game_settings')) {
            GameSetting::set('maintenance_mode_enabled', '1', 'Onderhoudsmodus status');
            GameSetting::set('maintenance_mode_metadata', $payload, 'Onderhoudsmodus metadata');
        }

        if (Schema::hasTable('sessions') && Schema::hasTable('users')) {
            $adminIds = DB::table('users')
                ->whereIn('role', ['owner', 'admin'])
                ->pluck('id')
                ->all();

            DB::table('sessions')
                ->where(function ($query) use ($adminIds): void {
                    $query->whereNull('user_id');
                    if (! empty($adminIds)) {
                        $query->orWhereNotIn('user_id', $adminIds);
                    } else {
                        $query->orWhereNotNull('user_id');
                    }
                })
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $lockFile = storage_path('framework/tbs_maintenance.json');
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        if (Schema::hasTable('game_settings')) {
            GameSetting::set('maintenance_mode_enabled', '0', 'Onderhoudsmodus status');
        }
    }
};
