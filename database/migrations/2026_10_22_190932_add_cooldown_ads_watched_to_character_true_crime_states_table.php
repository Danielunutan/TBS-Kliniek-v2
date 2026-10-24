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
        Schema::table('character_true_crime_states', function (Blueprint $table) {
            $table->unsignedSmallInteger('cooldown_ads_watched')->default(0)->after('cooldown_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_true_crime_states', function (Blueprint $table) {
            $table->dropColumn('cooldown_ads_watched');
        });
    }
};
