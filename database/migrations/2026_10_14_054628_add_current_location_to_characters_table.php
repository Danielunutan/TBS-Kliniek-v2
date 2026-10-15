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
        Schema::table('characters', function (Blueprint $table): void {
            if (! Schema::hasColumn('characters', 'current_location')) {
                $table->string('current_location', 100)->default('Paviljoen 1 (Gesloten)')->after('birthdate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table): void {
            if (Schema::hasColumn('characters', 'current_location')) {
                $table->dropColumn('current_location');
            }
        });
    }
};
