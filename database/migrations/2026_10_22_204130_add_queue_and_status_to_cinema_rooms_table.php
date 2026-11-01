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
                if (! Schema::hasColumn('cinema_rooms', 'is_closed')) {
                    $table->boolean('is_closed')->default(false)->after('is_private');
                }
                if (! Schema::hasColumn('cinema_rooms', 'closed_at')) {
                    $table->timestamp('closed_at')->nullable()->after('is_closed');
                }
                if (! Schema::hasColumn('cinema_rooms', 'video_queue')) {
                    $table->json('video_queue')->nullable()->after('youtube_video_id');
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
                $cols = array_filter(['is_closed', 'closed_at', 'video_queue'], fn ($col) => Schema::hasColumn('cinema_rooms', $col));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
