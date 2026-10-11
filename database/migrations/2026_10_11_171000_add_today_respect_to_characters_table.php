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
            if (! Schema::hasColumn('characters', 'today_respect')) {
                $table->decimal('today_respect', 10, 2)->default(0.00)->after('respect');
            }
            if (! Schema::hasColumn('characters', 'today_respect_date')) {
                $table->date('today_respect_date')->nullable()->after('today_respect');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['today_respect', 'today_respect_date']);
        });
    }
};
