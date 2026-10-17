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
        Schema::table('gangs', function (Blueprint $table) {
            $table->timestamp('vip_boost_until')->nullable()->after('shield_until');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gangs', function (Blueprint $table) {
            $table->dropColumn('vip_boost_until');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
