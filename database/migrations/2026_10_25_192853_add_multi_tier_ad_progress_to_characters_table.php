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
            $table->unsignedTinyInteger('ad_bullets_progress')->default(0)->after('ad_lottery_progress');
            $table->unsignedTinyInteger('ad_coma_progress')->default(0)->after('ad_bullets_progress');
            $table->unsignedTinyInteger('ad_prison_progress')->default(0)->after('ad_coma_progress');
            $table->unsignedTinyInteger('ad_vest_progress')->default(0)->after('ad_prison_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'ad_bullets_progress',
                'ad_coma_progress',
                'ad_prison_progress',
                'ad_vest_progress',
            ]);
        });
    }
};
