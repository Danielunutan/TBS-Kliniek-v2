<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore max_energy and max_health to 100 for all characters (including active VIPs) and clamp energy/health.
     */
    public function up(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        DB::table('characters')
            ->where('max_energy', '!=', 100)
            ->update(['max_energy' => 100]);

        DB::table('characters')
            ->where('energy', '>', 100)
            ->update(['energy' => 100]);

        DB::table('characters')
            ->where('max_health', '!=', 100)
            ->update(['max_health' => 100]);

        DB::table('characters')
            ->where('health', '>', 100)
            ->update(['health' => 100]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data normalization is non-reversible.
    }
};
