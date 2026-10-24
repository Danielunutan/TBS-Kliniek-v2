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
        Schema::table('cinema_rooms', function (Blueprint $table): void {
            $table->timestamp('video_started_at')->nullable()->after('youtube_video_id');
            $table->unsignedInteger('seek_offset')->default(0)->after('video_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cinema_rooms', function (Blueprint $table): void {
            $table->dropColumn(['video_started_at', 'seek_offset']);
        });
    }
};
