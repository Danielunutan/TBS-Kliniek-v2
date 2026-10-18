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
        if (! Schema::hasTable('halloween_quest_progress')) {
            Schema::create('halloween_quest_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->string('quest_key');
                $table->string('period_key'); // e.g. daily_2026-10-18 or weekly_2026-W42
                $table->unsignedInteger('current_count')->default(0);
                $table->unsignedInteger('target_count');
                $table->boolean('is_claimed')->default(false);
                $table->timestamp('claimed_at')->nullable();
                $table->timestamps();

                $table->unique(['character_id', 'quest_key', 'period_key'], 'h_quest_char_key_period_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halloween_quest_progress');
    }
};
