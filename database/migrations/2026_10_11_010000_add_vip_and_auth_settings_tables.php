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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'discord_id')) {
                $table->string('discord_id')->nullable()->after('google_id');
            }
            if (! Schema::hasColumn('users', 'is_staff')) {
                $table->boolean('is_staff')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->string('two_factor_secret')->nullable()->after('is_staff');
            }
            if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            }
        });

        Schema::table('characters', function (Blueprint $table) {
            if (! Schema::hasColumn('characters', 'vip_points')) {
                $table->integer('vip_points')->default(0)->after('stars');
            }
            if (! Schema::hasColumn('characters', 'is_vip')) {
                $table->boolean('is_vip')->default(false)->after('vip_points');
            }
            if (! Schema::hasColumn('characters', 'vip_expires_at')) {
                $table->timestamp('vip_expires_at')->nullable()->after('is_vip');
            }
            if (! Schema::hasColumn('characters', 'last_vip_reward_at')) {
                $table->timestamp('last_vip_reward_at')->nullable()->after('vip_expires_at');
            }
            if (! Schema::hasColumn('characters', 'username_changed_at')) {
                $table->timestamp('username_changed_at')->nullable()->after('name');
            }
        });

        Schema::create('game_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_settings');

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'vip_points',
                'is_vip',
                'vip_expires_at',
                'last_vip_reward_at',
                'username_changed_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'discord_id',
                'is_staff',
                'two_factor_secret',
                'two_factor_enabled',
            ]);
        });
    }
};
