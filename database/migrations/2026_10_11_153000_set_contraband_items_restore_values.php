<?php

use App\Models\Item;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Item::where('slug', 'sigaretten')->update([
            'energy_restore' => 20,
            'health_restore' => 10,
        ]);

        Item::where('slug', 'ritalin')->update([
            'energy_restore' => 35,
            'health_restore' => 15,
        ]);

        Item::where('slug', 'telefoon')->update([
            'energy_restore' => 50,
            'health_restore' => 25,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Item::whereIn('slug', ['sigaretten', 'ritalin', 'telefoon'])->update([
            'energy_restore' => 0,
            'health_restore' => 0,
        ]);
    }
};
