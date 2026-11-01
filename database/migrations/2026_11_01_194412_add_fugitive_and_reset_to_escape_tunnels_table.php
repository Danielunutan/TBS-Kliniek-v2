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
        Schema::table('escape_tunnels', function (Blueprint $table) {
            $table->timestamp('fugitive_until')->nullable()->after('escaped_at');
            $table->unsignedInteger('times_escaped')->default(0)->after('fugitive_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escape_tunnels', function (Blueprint $table) {
            $table->dropColumn(['fugitive_until', 'times_escaped']);
        });
    }
};
