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
        Schema::table('characters', function (Blueprint $table): void {
            if (! Schema::hasColumn('characters', 'breakout_attempts')) {
                $table->unsignedInteger('breakout_attempts')->default(0);
            }
            if (! Schema::hasColumn('characters', 'breakout_slay')) {
                $table->decimal('breakout_slay', 8, 2)->default(0.00);
            }
        });

        Schema::create('gang_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->string('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('gang_donations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->timestamps();
        });

        Schema::create('gang_alliances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ally_gang_id')->constrained('gangs')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gang_alliances');
        Schema::dropIfExists('gang_donations');
        Schema::dropIfExists('gang_requests');

        Schema::table('characters', function (Blueprint $table): void {
            $table->dropColumn(['breakout_attempts', 'breakout_slay']);
        });
    }
};
