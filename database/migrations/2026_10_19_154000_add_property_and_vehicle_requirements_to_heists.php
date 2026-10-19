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
        Schema::table('heists', function (Blueprint $table) {
            $table->json('required_property_keys')->nullable()->after('min_gang_members');
            $table->string('required_property_name')->nullable()->after('required_property_keys');
            $table->string('required_vehicle_name')->nullable()->after('required_vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('heists', function (Blueprint $table) {
            $table->dropColumn([
                'required_property_keys',
                'required_property_name',
                'required_vehicle_name',
            ]);
        });
    }
};
