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
        Schema::table('characters', function (Blueprint $table) {
            $table->timestamp('leave_until')->nullable()->after('prison_reason');
            $table->timestamp('sedated_until')->nullable()->after('leave_until');
            $table->timestamp('last_treatment_at')->nullable()->after('sedated_until');
            $table->unsignedInteger('treatment_score')->default(50)->after('last_treatment_at');
            $table->unsignedInteger('leave_trips_count')->default(0)->after('treatment_score');
            $table->json('treatment_history')->nullable()->after('leave_trips_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'leave_until',
                'sedated_until',
                'last_treatment_at',
                'treatment_score',
                'leave_trips_count',
                'treatment_history',
            ]);
        });
    }
};
