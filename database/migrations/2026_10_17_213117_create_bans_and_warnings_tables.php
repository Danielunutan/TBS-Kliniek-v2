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
            $table->boolean('is_banned')->default(false)->after('is_onboarded');
            $table->timestamp('banned_until')->nullable()->after('is_banned');
            $table->text('ban_reason')->nullable()->after('banned_until');
            $table->foreignId('banned_by_id')->nullable()->after('ban_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('banned_at')->nullable()->after('banned_by_id');
        });

        Schema::create('user_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issuer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('banned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('banned_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('unbanned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unbanned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bans');
        Schema::dropIfExists('user_warnings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by_id']);
            $table->dropColumn(['is_banned', 'banned_until', 'ban_reason', 'banned_by_id', 'banned_at']);
        });
    }
};
