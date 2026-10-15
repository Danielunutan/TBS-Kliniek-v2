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
        Schema::table('characters', function (Blueprint $table) {
            $table->unsignedInteger('ads_watched_today')->default(0)->after('daily_spins_today');
            $table->unsignedInteger('ads_watched_total')->default(0)->after('ads_watched_today');
            $table->timestamp('last_ad_watched_at')->nullable()->after('ads_watched_total');
            $table->string('last_ad_date', 10)->nullable()->after('last_ad_watched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'ads_watched_today',
                'ads_watched_total',
                'last_ad_watched_at',
                'last_ad_date',
            ]);
        });
    }
};
