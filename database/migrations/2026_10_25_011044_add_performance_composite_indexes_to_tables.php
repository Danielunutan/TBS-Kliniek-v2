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
        Schema::table('game_notifications', function (Blueprint $table) {
            $table->index(['character_id', 'is_read'], 'gn_char_read_idx');
        });

        Schema::table('training_logs', function (Blueprint $table) {
            $table->index(['character_id', 'created_at'], 'tl_char_created_idx');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->index(['is_sold', 'item_type'], 'ml_sold_type_idx');
            $table->index(['seller_id', 'is_sold'], 'ml_seller_sold_idx');
        });

        Schema::table('vault_codes', function (Blueprint $table) {
            $table->index(['character_id', 'is_redeemed'], 'vc_char_redeemed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vault_codes', function (Blueprint $table) {
            $table->dropIndex('vc_char_redeemed_idx');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropIndex('ml_sold_type_idx');
            $table->dropIndex('ml_seller_sold_idx');
        });

        Schema::table('training_logs', function (Blueprint $table) {
            $table->dropIndex('tl_char_created_idx');
        });

        Schema::table('game_notifications', function (Blueprint $table) {
            $table->dropIndex('gn_char_read_idx');
        });
    }
};
