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
        Schema::table('marketplace_listings', function (Blueprint $table): void {
            if (! Schema::hasColumn('marketplace_listings', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('item_type');
            }
            if (! Schema::hasColumn('marketplace_listings', 'metadata')) {
                $table->json('metadata')->nullable()->after('currency');
            }
        });

        Schema::table('character_vehicles', function (Blueprint $table): void {
            if (! Schema::hasColumn('character_vehicles', 'is_for_sale')) {
                $table->boolean('is_for_sale')->default(false)->after('is_active');
            }
        });

        Schema::table('gang_properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('gang_properties', 'is_for_sale')) {
                $table->boolean('is_for_sale')->default(false)->after('production_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gang_properties', function (Blueprint $table): void {
            if (Schema::hasColumn('gang_properties', 'is_for_sale')) {
                $table->dropColumn('is_for_sale');
            }
        });

        Schema::table('character_vehicles', function (Blueprint $table): void {
            if (Schema::hasColumn('character_vehicles', 'is_for_sale')) {
                $table->dropColumn('is_for_sale');
            }
        });

        Schema::table('marketplace_listings', function (Blueprint $table): void {
            if (Schema::hasColumn('marketplace_listings', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('marketplace_listings', 'related_id')) {
                $table->dropColumn('related_id');
            }
        });
    }
};
