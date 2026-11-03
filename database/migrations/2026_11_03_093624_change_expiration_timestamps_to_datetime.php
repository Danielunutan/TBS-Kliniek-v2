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
                if (Schema::hasColumn('characters', 'protection_until')) {
                    $table->timestamp('protection_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'cash_protection_until')) {
                    $table->timestamp('cash_protection_until')->nullable()->change();
                }
                if (Schema::hasColumn('characters', 'prison_until')) {
                    $table->timestamp('prison_until')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('user_bans') && Schema::hasColumn('user_bans', 'banned_until')) {
            Schema::table('user_bans', function (Blueprint $table) {
                $table->timestamp('banned_until')->nullable()->change();
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'banned_until')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('banned_until')->nullable()->change();
            });
        }

        if (Schema::hasTable('gangs') && Schema::hasColumn('gangs', 'vip_boost_until')) {
            Schema::table('gangs', function (Blueprint $table) {
                $table->timestamp('vip_boost_until')->nullable()->change();
            });
        }
    }
};
