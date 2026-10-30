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
        if (Schema::hasTable('event_cash_earnings')) {
            return;
        }

        Schema::create('event_cash_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('source', 50)->default('in_game');
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['created_at', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_cash_earnings');
    }
};
