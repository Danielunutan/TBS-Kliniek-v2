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
        Schema::create('cpalead_conversions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('lead_id')->unique();
            $table->string('subid')->nullable();
            $table->string('subid2')->nullable();
            $table->decimal('payout', 10, 4)->default(0);
            $table->string('campaign_id')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('reward_granted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpalead_conversions');
    }
};
