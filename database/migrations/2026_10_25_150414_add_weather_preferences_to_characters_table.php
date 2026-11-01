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
        if (Schema::hasTable('characters')) {
            Schema::table('characters', function (Blueprint $table) {
                if (! Schema::hasColumn('characters', 'weather_location')) {
                    $table->string('weather_location')->nullable()->after('unlocked_widgets');
                }
                if (! Schema::hasColumn('characters', 'weather_lat')) {
                    $table->decimal('weather_lat', 8, 4)->nullable()->after('weather_location');
                }
                if (! Schema::hasColumn('characters', 'weather_lon')) {
                    $table->decimal('weather_lon', 8, 4)->nullable()->after('weather_lat');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('characters')) {
            Schema::table('characters', function (Blueprint $table) {
                $cols = array_filter(['weather_location', 'weather_lat', 'weather_lon'], fn ($col) => Schema::hasColumn('characters', $col));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
