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
        if (! Schema::hasColumn('characters', 'title')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->string('title')->nullable()->default('Modelpatiënt')->after('name');
            });
        }

        if (! Schema::hasTable('profile_respects')) {
            Schema::create('profile_respects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('giver_id')->constrained('characters')->cascadeOnDelete();
                $table->foreignId('receiver_id')->constrained('characters')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['giver_id', 'receiver_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_respects');

        if (Schema::hasColumn('characters', 'title')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dropColumn('title');
            });
        }
    }
};
