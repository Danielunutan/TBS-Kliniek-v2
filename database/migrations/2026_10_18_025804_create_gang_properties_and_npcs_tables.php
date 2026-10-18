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
        Schema::create('gang_properties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->string('property_key');
            $table->string('name');
            $table->string('type'); // storage, production, luxury
            $table->unsignedInteger('level')->default(1);
            $table->bigInteger('purchase_price');
            $table->integer('storage_capacity')->default(0);
            $table->integer('vehicle_slots')->default(0);
            $table->integer('npc_capacity')->default(0);
            $table->string('production_status')->default('idle'); // idle, producing, ready
            $table->integer('production_duration_minutes')->default(0);
            $table->bigInteger('production_reward_cash')->default(0);
            $table->integer('production_reward_respect')->default(0);
            $table->timestamp('production_started_at')->nullable();
            $table->timestamp('production_ends_at')->nullable();
            $table->string('assigned_npc_key')->nullable();
            $table->timestamps();
        });

        Schema::create('gang_npcs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->string('npc_key');
            $table->string('name');
            $table->string('role_title');
            $table->string('avatar')->default('characters/avatar-default.png');
            $table->string('bonus_type'); // production_yield, production_speed, defense
            $table->integer('bonus_value')->default(25); // percentage e.g. 25 = +25%
            $table->unsignedBigInteger('assigned_property_id')->nullable();
            $table->timestamp('hired_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gang_npcs');
        Schema::dropIfExists('gang_properties');
    }
};
