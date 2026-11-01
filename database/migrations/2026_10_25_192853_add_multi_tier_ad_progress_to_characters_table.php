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
                if (! Schema::hasColumn('characters', 'ad_bullets_progress')) {
                    $table->unsignedTinyInteger('ad_bullets_progress')->default(0)->after('ad_lottery_progress');
                }
                if (! Schema::hasColumn('characters', 'ad_coma_progress')) {
                    $table->unsignedTinyInteger('ad_coma_progress')->default(0)->after('ad_bullets_progress');
                }
                if (! Schema::hasColumn('characters', 'ad_prison_progress')) {
                    $table->unsignedTinyInteger('ad_prison_progress')->default(0)->after('ad_coma_progress');
                }
                if (! Schema::hasColumn('characters', 'ad_vest_progress')) {
                    $table->unsignedTinyInteger('ad_vest_progress')->default(0)->after('ad_prison_progress');
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
                $cols = array_filter([
                    'ad_bullets_progress',
                    'ad_coma_progress',
                    'ad_prison_progress',
                    'ad_vest_progress',
                ], fn ($col) => Schema::hasColumn('characters', $col));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
