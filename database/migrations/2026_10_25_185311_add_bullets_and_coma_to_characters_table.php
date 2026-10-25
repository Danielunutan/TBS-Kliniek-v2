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
            $table->unsignedInteger('bullets')->default(0)->after('respect');
            $table->timestamp('vest_until')->nullable()->after('bullets');
            $table->timestamp('coma_until')->nullable()->after('vest_until');
            $table->unsignedInteger('kills_count')->default(0)->after('coma_until');
            $table->unsignedInteger('deaths_count')->default(0)->after('kills_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['bullets', 'vest_until', 'coma_until', 'kills_count', 'deaths_count']);
        });
    }
};
