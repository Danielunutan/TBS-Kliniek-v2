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
        Schema::table('character_vehicles', function (Blueprint $table) {
            $table->unsignedInteger('tuned_speed')->nullable()->after('vehicle_id');
        });

        Schema::table('heists', function (Blueprint $table) {
            $table->boolean('is_gang_heist')->default(false)->after('difficulty');
            $table->unsignedInteger('min_gang_members')->default(1)->after('is_gang_heist');
            $table->unsignedInteger('gang_respect_reward')->default(0)->after('respect_reward');
            $table->unsignedInteger('gang_treasury_share_pct')->default(50)->after('gang_respect_reward');
        });

        Schema::table('heist_logs', function (Blueprint $table) {
            $table->foreignId('gang_id')->nullable()->constrained()->nullOnDelete()->after('vehicle_id');
            $table->unsignedBigInteger('gang_cash_earned')->default(0)->after('cash_earned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_vehicles', function (Blueprint $table) {
            $table->dropColumn('tuned_speed');
        });

        Schema::table('heists', function (Blueprint $table) {
            $table->dropColumn([
                'is_gang_heist',
                'min_gang_members',
                'gang_respect_reward',
                'gang_treasury_share_pct',
            ]);
        });

        Schema::table('heist_logs', function (Blueprint $table) {
            $table->dropForeign(['gang_id']);
            $table->dropColumn(['gang_id', 'gang_cash_earned']);
        });
    }
};
