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
            $table->timestamp('last_hack_at')->nullable()->after('last_daily_spin_at');
            $table->unsignedInteger('hacks_completed_total')->default(0)->after('last_hack_at');
        });

        Schema::create('hacking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->string('reward_id');
            $table->string('reward_label');
            $table->boolean('vip_bonus_applied')->default(false);
            $table->string('vip_bonus_label')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hacking_logs');

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['last_hack_at', 'hacks_completed_total']);
        });
    }
};
