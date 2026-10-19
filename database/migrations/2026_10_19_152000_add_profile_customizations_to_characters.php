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
            if (! Schema::hasColumn('characters', 'profile_sticker')) {
                $table->string('profile_sticker', 60)->nullable()->after('profile_color');
            }
            if (! Schema::hasColumn('characters', 'profile_bg_theme')) {
                $table->string('profile_bg_theme', 40)->default('default')->after('profile_sticker');
            }
            if (! Schema::hasColumn('characters', 'unlocked_stickers')) {
                $table->json('unlocked_stickers')->nullable()->after('profile_bg_theme');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'unlocked_stickers')) {
                $table->dropColumn('unlocked_stickers');
            }
            if (Schema::hasColumn('characters', 'profile_bg_theme')) {
                $table->dropColumn('profile_bg_theme');
            }
            if (Schema::hasColumn('characters', 'profile_sticker')) {
                $table->dropColumn('profile_sticker');
            }
        });
    }
};
