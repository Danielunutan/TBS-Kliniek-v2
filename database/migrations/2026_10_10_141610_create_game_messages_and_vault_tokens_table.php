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
            $table->unsignedBigInteger('vault_tokens')->default(5)->after('bank');
            $table->json('claimed_level_rewards')->nullable()->after('vault_tokens');
        });

        Schema::create('game_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('sender_name')->default('Directie TBS Kliniek');
            $table->string('subject');
            $table->text('content');
            $table->string('type')->default('system'); // system, theft, jail, level, player
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_messages');

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['vault_tokens', 'claimed_level_rewards']);
        });
    }
};
