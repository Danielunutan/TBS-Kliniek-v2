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
        Schema::table('gangs', function (Blueprint $table): void {
            $table->integer('leave_fee_multiplier')->default(0)->change();
        });

        // Cap any existing gangs that have a multiplier > 50 down to 50
        DB::table('gangs')
            ->where('leave_fee_multiplier', '>', 50)
            ->update(['leave_fee_multiplier' => 50]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gangs', function (Blueprint $table): void {
            $table->integer('leave_fee_multiplier')->default(0)->change();
        });
    }
};
