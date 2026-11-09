<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add cell_upgrades JSON to characters
        if (Schema::hasTable('characters') && ! Schema::hasColumn('characters', 'cell_upgrades')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->json('cell_upgrades')->nullable()->after('perks');
            });
        }

        // 2. Add war_dominance_until to gangs
        if (Schema::hasTable('gangs') && ! Schema::hasColumn('gangs', 'war_dominance_until')) {
            Schema::table('gangs', function (Blueprint $table) {
                $table->timestamp('war_dominance_until')->nullable()->after('last_attack_at');
            });
        }

        // 3. Create gang_wars table for structured 24-hour wars
        if (! Schema::hasTable('gang_wars')) {
            Schema::create('gang_wars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('challenger_gang_id')->constrained('gangs')->cascadeOnDelete();
                $table->foreignId('defender_gang_id')->constrained('gangs')->cascadeOnDelete();
                $table->string('status', 30)->default('active')->index();
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('ends_at')->nullable()->index();
                $table->integer('challenger_score')->default(0);
                $table->integer('defender_score')->default(0);
                $table->foreignId('winner_gang_id')->nullable()->constrained('gangs')->nullOnDelete();
                $table->unsignedBigInteger('war_pot')->default(500000);
                $table->integer('respect_pot')->default(100);
                $table->text('summary')->nullable();
                $table->timestamps();

                $table->index(['challenger_gang_id', 'status']);
                $table->index(['defender_gang_id', 'status']);
            });
        }

        // 4. Ensure De Smokkellijn control point exists
        if (Schema::hasTable('control_points')) {
            $exists = DB::table('control_points')->where('key', 'smokkellijn')->exists();
            if (! $exists) {
                $now = now();
                DB::table('control_points')->insert([
                    'key' => 'smokkellijn',
                    'name' => 'De Ondergrondse Smokkellijn',
                    'location_name' => 'Smokkelcorridor & Buitenhek',
                    'page_route' => 'smuggle.index',
                    'description' => 'De geheime smokkelroute voor contrabande en medicijnen. Wie deze lijn controleert beheerst de smokkel van de kliniek.',
                    'icon' => 'fa-route',
                    'cash_per_minute' => 75,
                    'stars_per_minute' => 0,
                    'total_captures' => 0,
                    'energy_cost' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'cell_upgrades')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dropColumn('cell_upgrades');
            });
        }

        if (Schema::hasTable('gangs') && Schema::hasColumn('gangs', 'war_dominance_until')) {
            Schema::table('gangs', function (Blueprint $table) {
                $table->dropColumn('war_dominance_until');
            });
        }

        Schema::dropIfExists('gang_wars');

        if (Schema::hasTable('control_points')) {
            DB::table('control_points')->where('key', 'smokkellijn')->delete();
        }
    }
};
