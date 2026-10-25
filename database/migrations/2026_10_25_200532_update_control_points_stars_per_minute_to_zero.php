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
        if (Schema::hasTable('control_points')) {
            Schema::table('control_points', function (Blueprint $table): void {
                $table->integer('stars_per_minute')->default(0)->change();
            });

            DB::table('control_points')->update(['stars_per_minute' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('control_points')) {
            Schema::table('control_points', function (Blueprint $table): void {
                $table->integer('stars_per_minute')->default(1)->change();
            });
        }
    }
};
