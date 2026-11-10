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
        if (Schema::hasTable('characters') && ! Schema::hasColumn('characters', 'wage_protection_until')) {
            Schema::table('characters', function (Blueprint $table): void {
                $table->dateTime('wage_protection_until')->nullable()->index()->after('cash_protection_until');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'wage_protection_until')) {
            Schema::table('characters', function (Blueprint $table): void {
                $table->dropIndex(['wage_protection_until']);
                $table->dropColumn(['wage_protection_until']);
            });
        }
    }
};
