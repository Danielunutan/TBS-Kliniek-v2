<?php

use App\Models\Character;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert any character stats above the level cap into XP and clamp to getMaxStatForLevel().
     */
    public function up(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        Character::query()
            ->whereRaw('strength > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('defense > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('stamina > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('intelligence > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('charisma > (100 + COALESCE(level, 1) * 50)')
            ->orWhereRaw('discipline > (100 + COALESCE(level, 1) * 50)')
            ->chunkById(100, function ($characters): void {
                foreach ($characters as $character) {
                    $character->syncStatsWithLevelCap();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data normalization is non-reversible.
    }
};
