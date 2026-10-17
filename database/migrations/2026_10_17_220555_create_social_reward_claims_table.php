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
        Schema::create('social_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 32);
            $table->unsignedInteger('reward_cash')->default(0);
            $table->unsignedInteger('reward_respect')->default(0);
            $table->unsignedInteger('reward_energy')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('claimed_at');
            $table->timestamps();

            $table->unique(['user_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_reward_claims');
    }
};
