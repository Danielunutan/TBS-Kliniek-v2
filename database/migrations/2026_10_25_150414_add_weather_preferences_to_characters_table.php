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
            $table->string('weather_location')->nullable()->after('unlocked_widgets');
            $table->decimal('weather_lat', 8, 4)->nullable()->after('weather_location');
            $table->decimal('weather_lon', 8, 4)->nullable()->after('weather_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['weather_location', 'weather_lat', 'weather_lon']);
        });
    }
};
