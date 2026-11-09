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
        Schema::table('character_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('character_vehicles', 'is_gang_owned')) {
                $table->boolean('is_gang_owned')->default(false)->after('in_gang_garage');
            }
            if (! Schema::hasColumn('character_vehicles', 'donated_at')) {
                $table->timestamp('donated_at')->nullable()->after('stored_at');
            }
            if (! Schema::hasColumn('character_vehicles', 'donated_by_character_id')) {
                $table->foreignId('donated_by_character_id')->nullable()->after('donated_at')->constrained('characters')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('character_vehicles', 'donated_by_character_id')) {
                $table->dropForeign(['donated_by_character_id']);
                $table->dropColumn('donated_by_character_id');
            }
            if (Schema::hasColumn('character_vehicles', 'donated_at')) {
                $table->dropColumn('donated_at');
            }
            if (Schema::hasColumn('character_vehicles', 'is_gang_owned')) {
                $table->dropColumn('is_gang_owned');
            }
        });
    }
};

