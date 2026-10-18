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
        Schema::create('control_points', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('location_name');
            $table->string('page_route')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-flag');
            $table->foreignId('current_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->foreignId('current_gang_id')->nullable()->constrained('gangs')->nullOnDelete();
            $table->timestamp('captured_at')->nullable();
            $table->integer('cash_per_minute')->default(50);
            $table->integer('stars_per_minute')->default(1);
            $table->integer('total_captures')->default(0);
            $table->integer('energy_cost')->default(2);
            $table->timestamps();
        });

        Schema::create('control_point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('control_point_id')->constrained('control_points')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('gang_id')->nullable()->constrained('gangs')->nullOnDelete();
            $table->timestamp('held_from');
            $table->timestamp('held_until');
            $table->integer('duration_minutes')->default(0);
            $table->integer('reward_cash')->default(0);
            $table->integer('reward_stars')->default(0);
            $table->foreignId('overtaken_by_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->timestamps();
        });

        // Seed initial control points
        $now = now();
        DB::table('control_points')->insert([
            [
                'key' => 'luchtplaats',
                'name' => 'Het Bankje bij het Hek',
                'location_name' => 'De Luchtplaats',
                'page_route' => 'fight.index',
                'description' => 'De meest strategische plek op de binnenplaats. Wie hier zit bepaalt de regels van de pauze.',
                'icon' => 'fa-smoking',
                'cash_per_minute' => 50,
                'stars_per_minute' => 1,
                'total_captures' => 0,
                'energy_cost' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'kantine',
                'name' => 'De Koffieautomaat Hoek',
                'location_name' => 'De Kantine & Shop',
                'page_route' => 'shop.index',
                'description' => 'Alle smokkelwaar en geruchten passeren langs de warme automaat. Eis jouw tol op!',
                'icon' => 'fa-mug-hot',
                'cash_per_minute' => 50,
                'stars_per_minute' => 1,
                'total_captures' => 0,
                'energy_cost' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'sportzaal',
                'name' => 'De Zware Halterbank',
                'location_name' => 'De Fitness & Sportzaal',
                'page_route' => 'training.index',
                'description' => 'Alleen de zwaargewichten durven deze bank op te eisen. Geef je spieren en reputatie status!',
                'icon' => 'fa-dumbbell',
                'cash_per_minute' => 50,
                'stars_per_minute' => 1,
                'total_captures' => 0,
                'energy_cost' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'depot',
                'name' => 'De Expeditie Laadkade',
                'location_name' => 'Centraal Depot & Smokkel',
                'page_route' => 'depot.index',
                'description' => 'De poort waar alle vracht binnenkomt. Houd toezicht en strijk elke minuut extra winst op.',
                'icon' => 'fa-boxes-stacked',
                'cash_per_minute' => 50,
                'stars_per_minute' => 1,
                'total_captures' => 0,
                'energy_cost' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'medicijnkluis',
                'name' => 'De Medicijnkluis Gang',
                'location_name' => 'De Kluis & Behandelvleugel',
                'page_route' => 'vault.index',
                'description' => 'De zwaar beveiligde corridor bij de kluizen. Laat zien dat beveiligers en rivalen jou hier niet wegkrijgen.',
                'icon' => 'fa-vault',
                'cash_per_minute' => 50,
                'stars_per_minute' => 1,
                'total_captures' => 0,
                'energy_cost' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_point_logs');
        Schema::dropIfExists('control_points');
    }
};
