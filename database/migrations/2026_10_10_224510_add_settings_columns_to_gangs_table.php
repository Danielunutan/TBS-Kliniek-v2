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
        Schema::table('gangs', function (Blueprint $table): void {
            if (! Schema::hasColumn('gangs', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (! Schema::hasColumn('gangs', 'banner')) {
                $table->string('banner')->nullable();
            }
            if (! Schema::hasColumn('gangs', 'leave_fee_multiplier')) {
                $table->integer('leave_fee_multiplier')->default(48);
            }
            if (! Schema::hasColumn('gangs', 'min_respect')) {
                $table->integer('min_respect')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gangs', function (Blueprint $table): void {
            $table->dropColumn(['avatar', 'banner', 'leave_fee_multiplier', 'min_respect']);
        });
    }
};
