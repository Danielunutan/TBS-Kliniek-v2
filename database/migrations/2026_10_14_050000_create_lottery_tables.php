<?php

use App\Models\LotteryRound;
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
        Schema::create('lottery_rounds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('round_number')->default(1);
            $table->unsignedBigInteger('jackpot')->default(25000);
            $table->unsignedBigInteger('tax_collected')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('draws_at')->nullable();
            $table->boolean('is_drawn')->default(false);
            $table->foreignId('winner_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->unsignedBigInteger('winner_payout')->nullable();
            $table->string('winning_ticket_number', 12)->nullable();
            $table->unsignedInteger('total_tickets_sold')->default(0);
            $table->timestamps();
        });

        Schema::create('lottery_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lottery_round_id')->constrained('lottery_rounds')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('ticket_number', 12);
            $table->string('currency', 10)->default('cash'); // 'cash' or 'stars'
            $table->unsignedInteger('price_paid')->default(1500);
            $table->timestamps();

            $table->index(['lottery_round_id', 'character_id']);
        });

        // Initialize round 1 with starting jackpot of €25.000, drawing 7 days from now
        LotteryRound::create([
            'round_number' => 1,
            'jackpot' => 25000,
            'tax_collected' => 0,
            'starts_at' => now(),
            'draws_at' => now()->addDays(7),
            'is_drawn' => false,
            'total_tickets_sold' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_tickets');
        Schema::dropIfExists('lottery_rounds');
    }
};
