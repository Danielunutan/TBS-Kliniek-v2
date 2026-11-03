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
        // Convert expiration/duration timestamps from 32-bit TIMESTAMP to DATETIME
        // on MySQL to prevent overflow errors for dates past 2038-01-19.
        if (Schema::hasTable('gangs') && Schema::hasColumn('gangs', 'vip_boost_until')) {
            Schema::table('gangs', function (Blueprint $table) {
                $table->dateTime('vip_boost_until')->nullable()->change();
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'banned_until')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('banned_until')->nullable()->change();
            });
        }

        if (Schema::hasTable('user_bans') && Schema::hasColumn('user_bans', 'banned_until')) {
            Schema::table('user_bans', function (Blueprint $table) {
                $table->dateTime('banned_until')->nullable()->change();
            });
        }

        if (Schema::hasTable('characters')) {
            Schema::table('characters', function (Blueprint $table) {
                if (Schema::hasColumn('characters', 'protection_until')) {
                    $table->dateTime('protection_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'cash_protection_until')) {
                    $table->dateTime('cash_protection_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'prison_until')) {
                    $table->dateTime('prison_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'leave_until')) {
                    $table->dateTime('leave_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'sedated_until')) {
                    $table->dateTime('sedated_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'vest_until')) {
                    $table->dateTime('vest_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'coma_until')) {
                    $table->dateTime('coma_until')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('gang_attacks') && Schema::hasColumn('gang_attacks', 'shield_until')) {
            Schema::table('gang_attacks', function (Blueprint $table) {
                $table->dateTime('shield_until')->nullable()->change();
            });
        }

        if (Schema::hasTable('escape_tunnels') && Schema::hasColumn('escape_tunnels', 'fugitive_until')) {
            Schema::table('escape_tunnels', function (Blueprint $table) {
                $table->dateTime('fugitive_until')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting DATETIME to 32-bit TIMESTAMP causes fatal MySQL Error 1292 on timestamps past 2038.
    }
};
