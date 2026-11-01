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
                if (! Schema::hasColumn('characters', 'bullets')) {
                    $table->unsignedInteger('bullets')->default(0)->after('respect');
                }
                if (! Schema::hasColumn('characters', 'vest_until')) {
                    $table->timestamp('vest_until')->nullable()->after('bullets');
                }
                if (! Schema::hasColumn('characters', 'coma_until')) {
                    $table->timestamp('coma_until')->nullable()->after('vest_until');
                }
                if (! Schema::hasColumn('characters', 'kills_count')) {
                    $table->unsignedInteger('kills_count')->default(0)->after('coma_until');
                }
                if (! Schema::hasColumn('characters', 'deaths_count')) {
                    $table->unsignedInteger('deaths_count')->default(0)->after('kills_count');
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
                $cols = array_filter(['bullets', 'vest_until', 'coma_until', 'kills_count', 'deaths_count'], fn ($col) => Schema::hasColumn('characters', $col));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
