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
            $table->boolean('is_closed')->default(false)->after('is_private');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
            $table->json('video_queue')->nullable()->after('youtube_video_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cinema_rooms', function (Blueprint $table): void {
            $table->dropColumn(['is_closed', 'closed_at', 'video_queue']);
        });
    }
};
