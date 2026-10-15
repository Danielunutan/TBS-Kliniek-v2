<?php

use App\Models\Item;
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
            if (! Schema::hasColumn('characters', 'dagbesteding_shifts_completed')) {
                $table->unsignedInteger('dagbesteding_shifts_completed')->default(0)->after('missions_completed');
            }
            if (! Schema::hasColumn('characters', 'dagbesteding_earned_total')) {
                $table->unsignedBigInteger('dagbesteding_earned_total')->default(0)->after('dagbesteding_shifts_completed');
            }
            if (! Schema::hasColumn('characters', 'last_infirmary_visit_at')) {
                $table->timestamp('last_infirmary_visit_at')->nullable()->after('last_active_at');
            }
            if (! Schema::hasColumn('characters', 'palmed_pills_count')) {
                $table->unsignedInteger('palmed_pills_count')->default(0)->after('dagbesteding_earned_total');
            }
        });

        // Seed specialized medication items
        $medications = [
            [
                'name' => 'Strip Oxazepam (10x)',
                'slug' => 'strip-oxazepam',
                'description' => 'Gepalmde kalmeringstabletten van zuster Ingrid. Herstelt direct 40 Gezondheid.',
                'category' => 'consumable',
                'rarity' => 'uncommon',
                'price_kc' => 850,
                'energy_restore' => 0,
                'health_restore' => 40,
                'icon' => 'fa-pills',
                'is_buyable' => false,
            ],
            [
                'name' => 'Strip Ritalin (10x)',
                'slug' => 'strip-ritalin',
                'description' => 'Gepalmde concentratietabletten. Geeft een directe boost van 40 Energie om extra te werken of hosselen.',
                'category' => 'consumable',
                'rarity' => 'rare',
                'price_kc' => 1200,
                'energy_restore' => 40,
                'health_restore' => 0,
                'icon' => 'fa-capsules',
                'is_buyable' => false,
            ],
            [
                'name' => 'Haloperidol Forte Depot',
                'slug' => 'haloperidol-forte',
                'description' => 'Zware stemmingsremmer uit het dokterskastje. Geeft brute vechtfocus (+12 Kracht & +8 Verdediging).',
                'category' => 'consumable',
                'rarity' => 'epic',
                'price_kc' => 2500,
                'energy_restore' => 20,
                'health_restore' => 25,
                'strength_bonus' => 12,
                'defense_bonus' => 8,
                'icon' => 'fa-prescription-bottle-medical',
                'is_buyable' => false,
            ],
        ];

        foreach ($medications as $med) {
            Item::firstOrCreate(['slug' => $med['slug']], $med);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table): void {
            $table->dropColumn([
                'dagbesteding_shifts_completed',
                'dagbesteding_earned_total',
                'last_infirmary_visit_at',
                'palmed_pills_count',
            ]);
        });
    }
};
