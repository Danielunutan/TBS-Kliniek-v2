<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('user')->after('is_staff');
            }
        });

        // Set owner and admin roles for existing accounts
        DB::table('users')
            ->whereIn('email', ['admin@tbskliniek.nl', 'danielunutan@icloud.com'])
            ->update(['role' => 'owner', 'is_staff' => true]);

        DB::table('users')
            ->where('is_staff', true)
            ->whereNotIn('email', ['admin@tbskliniek.nl', 'danielunutan@icloud.com'])
            ->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
