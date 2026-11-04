<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_memory_stats', function (Blueprint $table): void {
            $table->json('active_game')->nullable();
        });

        Schema::create('registration_ip_locks', function (Blueprint $table): void {
            $table->string('ip_hash', 64)->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_ip_locks');
        Schema::table('character_memory_stats', function (Blueprint $table): void {
            $table->dropColumn('active_game');
        });
    }
};
