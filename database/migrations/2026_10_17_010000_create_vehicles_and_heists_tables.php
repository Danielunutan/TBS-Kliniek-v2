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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('auto'); // auto, motor, boot, helikopter, vliegtuig
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('price_cash')->default(10000);
            $table->unsignedInteger('price_stars')->default(0);
            $table->unsignedInteger('speed_pct')->default(60); // 1-100%
            $table->unsignedInteger('reliability_pct')->default(75); // 1-100%
            $table->unsignedInteger('min_level')->default(1);
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-car');
            $table->string('badge_color')->default('purple');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('character_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_condition')->default(100); // 0-100%
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('total_heists_done')->default(0);
            $table->timestamps();
        });

        Schema::create('heists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('difficulty')->default('normaal'); // makkelijk, normaal, zwaar, extreem, legendarisch
            $table->unsignedInteger('min_level')->default(1);
            $table->unsignedInteger('energy_cost')->default(15);
            $table->unsignedInteger('required_mask_count')->default(1);
            $table->string('required_vehicle_type')->default('auto'); // auto, any, helikopter, boot
            $table->unsignedInteger('min_speed_required')->default(40);
            $table->unsignedInteger('min_condition_required')->default(30);
            $table->unsignedBigInteger('payout_min')->default(5000);
            $table->unsignedBigInteger('payout_max')->default(25000);
            $table->unsignedInteger('xp_reward')->default(50);
            $table->unsignedInteger('respect_reward')->default(10);
            $table->unsignedInteger('isolation_risk_minutes')->default(15);
            $table->string('icon')->default('fa-vault');
            $table->timestamps();
        });

        Schema::create('heist_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('heist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('success')->default(false);
            $table->unsignedBigInteger('cash_earned')->default(0);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->unsignedInteger('respect_earned')->default(0);
            $table->unsignedInteger('condition_loss')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heist_logs');
        Schema::dropIfExists('heists');
        Schema::dropIfExists('character_vehicles');
        Schema::dropIfExists('vehicles');
    }
};
