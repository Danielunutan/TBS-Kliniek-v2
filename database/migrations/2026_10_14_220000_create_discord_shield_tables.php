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
        // 1. Settings (Bot token, guild id, role ids, policy toggles)
        Schema::create('discord_shield_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 2. Verifications (History of verified & flagged accounts)
        Schema::create('discord_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id')->index();
            $table->string('username');
            $table->string('discriminator')->nullable();
            $table->string('avatar')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->index();
            $table->string('country_code', 8)->nullable();
            $table->boolean('is_vpn')->default(false);
            $table->string('vpn_provider')->nullable();
            $table->string('fingerprint_hash', 64)->index();
            $table->json('fingerprint_raw')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('status')->default('verified'); // verified, alt_detected, vpn_blocked, banned, quarantined
            $table->string('alt_of_discord_id')->nullable()->index();
            $table->string('alt_of_username')->nullable();
            $table->unsignedTinyInteger('risk_score')->default(0); // 0-100
            $table->boolean('turnstile_passed')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 3. Audit Logs (Rich logging for staff review)
        Schema::create('discord_shield_logs', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id')->nullable()->index();
            $table->string('username')->nullable();
            $table->string('event'); // verification_success, alt_blocked, vpn_blocked, manual_approval, ban_enforced, bot_error
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->string('fingerprint_hash', 64)->nullable()->index();
            $table->string('severity')->default('info'); // info, success, warning, danger
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 4. Blacklist / Bans (Hardware & IP bans)
        Schema::create('discord_shield_bans', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // discord_id, fingerprint, ip_address
            $table->string('value')->index();
            $table->text('reason')->nullable();
            $table->string('banned_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_shield_bans');
        Schema::dropIfExists('discord_shield_logs');
        Schema::dropIfExists('discord_verifications');
        Schema::dropIfExists('discord_shield_settings');
    }
};
