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
            if (! Schema::hasColumn('characters', 'daily_deaths_count')) {
                $table->unsignedInteger('daily_deaths_count')->default(0)->after('deaths_count');
            }
            if (! Schema::hasColumn('characters', 'daily_deaths_date')) {
                $table->string('daily_deaths_date', 10)->nullable()->after('daily_deaths_count');
            }
        });

        if (Schema::hasTable('blood_feuds')) {
            return;
        }

        Schema::create('blood_feuds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attacker_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('victim_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('victim_gang_id')->nullable()->constrained('gangs')->nullOnDelete();
            $table->timestamp('expires_at')->index();
            $table->boolean('is_avenged')->default(false)->index();
            $table->foreignId('avenged_by_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->timestamp('avenged_at')->nullable();
            $table->timestamps();

            $table->index(['attacker_id', 'is_avenged', 'expires_at']);
            $table->index(['victim_id', 'is_avenged']);
            $table->index(['victim_gang_id', 'is_avenged']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_feuds');

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['daily_deaths_count', 'daily_deaths_date']);
        });
    }
};
