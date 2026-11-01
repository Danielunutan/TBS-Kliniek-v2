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
        if (Schema::hasTable('cinema_rooms')) {
            Schema::table('cinema_rooms', function (Blueprint $table): void {
                if (! Schema::hasColumn('cinema_rooms', 'video_started_at')) {
                    $table->timestamp('video_started_at')->nullable()->after('youtube_video_id');
                }
                if (! Schema::hasColumn('cinema_rooms', 'seek_offset')) {
                    $table->unsignedInteger('seek_offset')->default(0)->after('video_started_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cinema_rooms')) {
            Schema::table('cinema_rooms', function (Blueprint $table): void {
                $cols = array_filter(['video_started_at', 'seek_offset'], fn ($col) => Schema::hasColumn('cinema_rooms', $col));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
