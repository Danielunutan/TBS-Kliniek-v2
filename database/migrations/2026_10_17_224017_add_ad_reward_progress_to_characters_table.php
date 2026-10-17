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
            $table->unsignedTinyInteger('ad_mask_progress')->default(0)->after('ads_watched_total');
            $table->unsignedTinyInteger('ad_vault_progress')->default(0)->after('ad_mask_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['ad_mask_progress', 'ad_vault_progress']);
        });
    }
};
