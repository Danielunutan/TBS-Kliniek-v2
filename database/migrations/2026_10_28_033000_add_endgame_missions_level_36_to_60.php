<?php

use App\Models\Mission;
use App\Support\MissionCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (MissionCatalog::all() as $missionData) {
            Mission::updateOrCreate(
                ['slug' => $missionData['slug']],
                $missionData
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Mission::where('sort_order', '>', 50)->delete();
    }
};
