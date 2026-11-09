<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('gang_npcs')) {
            Schema::table('gang_npcs', function (Blueprint $table): void {
                if (! Schema::hasColumn('gang_npcs', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('hired_at');
                    $table->index(['gang_id', 'expires_at']);
                }
            });

            // For existing permanent NPCs, calculate their expiration based on hired_at + 30 minutes.
            // Any that were hired more than 30 minutes ago are purged to free up clogged gang slots immediately.
            DB::table('gang_npcs')
                ->whereNull('expires_at')
                ->where('hired_at', '<=', now()->subMinutes(30))
                ->delete();

            // Any recent permanent hires get the remainder of their 30-minute window.
            DB::table('gang_npcs')
                ->whereNull('expires_at')
                ->update([
                    'expires_at' => now()->addMinutes(30),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gang_npcs') && Schema::hasColumn('gang_npcs', 'expires_at')) {
            Schema::table('gang_npcs', function (Blueprint $table): void {
                $table->dropIndex(['gang_id', 'expires_at']);
                $table->dropColumn('expires_at');
            });
        }
    }
};
