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
        Schema::create('gang_attacks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attacker_gang_id')->constrained('gangs')->cascadeOnDelete();
            $table->foreignId('target_gang_id')->constrained('gangs')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->bigInteger('attacker_power');
            $table->bigInteger('target_defense');
            $table->boolean('won');
            $table->bigInteger('stolen_cash')->default(0);
            $table->bigInteger('defense_damage')->default(0);
            $table->bigInteger('power_damage')->default(0);
            $table->decimal('respect_gain', 8, 2)->default(0.00);
            $table->text('summary');
            $table->timestamps();
        });

        Schema::table('gangs', function (Blueprint $table): void {
            if (! Schema::hasColumn('gangs', 'last_attack_at')) {
                $table->timestamp('last_attack_at')->nullable();
            }
            if (! Schema::hasColumn('gangs', 'shield_until')) {
                $table->timestamp('shield_until')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gangs', function (Blueprint $table): void {
            $table->dropColumn(['last_attack_at', 'shield_until']);
        });

        Schema::dropIfExists('gang_attacks');
    }
};
