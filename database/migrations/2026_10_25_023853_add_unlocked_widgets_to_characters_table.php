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
        if (Schema::hasTable('characters') && ! Schema::hasColumn('characters', 'unlocked_widgets')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->json('unlocked_widgets')->nullable()->after('stars');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'unlocked_widgets')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dropColumn('unlocked_widgets');
            });
        }
    }
};
