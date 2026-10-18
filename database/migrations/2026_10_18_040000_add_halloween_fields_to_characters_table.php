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
        Schema::table('characters', function (Blueprint $table) {
            if (! Schema::hasColumn('characters', 'pumpkin_seeds')) {
                $table->unsignedInteger('pumpkin_seeds')->default(0)->after('stars');
            }
            if (! Schema::hasColumn('characters', 'equipped_border')) {
                $table->string('equipped_border')->nullable()->after('pumpkin_seeds');
            }
            if (! Schema::hasColumn('characters', 'unlocked_borders')) {
                $table->json('unlocked_borders')->nullable()->after('equipped_border');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'unlocked_borders')) {
                $table->dropColumn('unlocked_borders');
            }
            if (Schema::hasColumn('characters', 'equipped_border')) {
                $table->dropColumn('equipped_border');
            }
            if (Schema::hasColumn('characters', 'pumpkin_seeds')) {
                $table->dropColumn('pumpkin_seeds');
            }
        });
    }
};
