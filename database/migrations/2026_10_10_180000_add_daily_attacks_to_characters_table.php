<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table): void {
            $table->unsignedInteger('daily_attacks_count')->default(0)->after('xxl_cases');
            $table->string('daily_attacks_date', 10)->nullable()->after('daily_attacks_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table): void {
            $table->dropColumn(['daily_attacks_count', 'daily_attacks_date']);
        });
    }
};
