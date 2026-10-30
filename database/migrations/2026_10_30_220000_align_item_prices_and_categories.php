<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align item categories, prices, and icons with clinic shop and depot rules.
     */
    public function up(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        // 1. Align Oude Sleutel as equipment (+10 Intelligence) with Free FontAwesome icon.
        DB::table('items')
            ->where('slug', 'oude-sleutel')
            ->update([
                'category' => 'equipment',
                'price_kc' => 800,
                'intelligence_bonus' => 10,
                'icon' => 'fa-key',
                'description' => 'Een roestige sleutel gevonden bij de wasserij (+10 Intelligentie).',
            ]);

        // 2. Align De Sleutel van Paviljoen 13 as equipment (+12 Intelligence).
        DB::table('items')
            ->where('slug', 'sleutel-paviljoen-13')
            ->update([
                'category' => 'equipment',
                'price_kc' => 5000,
                'intelligence_bonus' => 12,
                'icon' => 'fa-key',
            ]);

        // 3. Align Gouden Frikandel as consumable (+50 HP).
        DB::table('items')
            ->where('slug', 'gouden-frikandel')
            ->update([
                'category' => 'consumable',
                'price_kc' => 1500,
                'health_restore' => 50,
                'description' => 'Het legendarische statussymbool van de kantine. Herstelt direct +50 HP.',
            ]);

        // 4. Align Zwart Bivakmuts Masker price to €2.500 so its €1.500 resale value (60%) cannot be arbitraged.
        DB::table('items')
            ->where('slug', 'bivakmuts-masker')
            ->update([
                'price_kc' => 2500,
            ]);

        // 5. Ensure Smokkelpakketje is not buyable in the regular shop.
        DB::table('items')
            ->where('slug', 'smokkelpakketje')
            ->update([
                'is_buyable' => false,
            ]);

        // 6. Replace FontAwesome Pro-only icons with FontAwesome 6 Free equivalents.
        DB::table('items')->where('icon', 'fa-key-skeleton')->update(['icon' => 'fa-key']);
        DB::table('items')->where('icon', 'fa-mitten')->update(['icon' => 'fa-hand-fist']);
        DB::table('items')->where('icon', 'fa-vest')->update(['icon' => 'fa-shirt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        DB::table('items')
            ->where('slug', 'bivakmuts-masker')
            ->update(['price_kc' => 650]);
    }
};
