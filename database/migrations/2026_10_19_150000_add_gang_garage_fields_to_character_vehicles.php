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
            if (! Schema::hasColumn('character_vehicles', 'gang_id')) {
                $table->foreignId('gang_id')->nullable()->after('character_id')->constrained('gangs')->nullOnDelete();
            }
            if (! Schema::hasColumn('character_vehicles', 'in_gang_garage')) {
                $table->boolean('in_gang_garage')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('character_vehicles', 'stored_at')) {
                $table->timestamp('stored_at')->nullable()->after('in_gang_garage');
            }
            if (! Schema::hasColumn('character_vehicles', 'stored_by_character_id')) {
                $table->foreignId('stored_by_character_id')->nullable()->after('stored_at')->constrained('characters')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('character_vehicles', 'stored_by_character_id')) {
                $table->dropForeign(['stored_by_character_id']);
                $table->dropColumn('stored_by_character_id');
            }
            if (Schema::hasColumn('character_vehicles', 'stored_at')) {
                $table->dropColumn('stored_at');
            }
            if (Schema::hasColumn('character_vehicles', 'in_gang_garage')) {
                $table->dropColumn('in_gang_garage');
            }
            if (Schema::hasColumn('character_vehicles', 'gang_id')) {
                $table->dropForeign(['gang_id']);
                $table->dropColumn('gang_id');
            }
        });
    }
};
