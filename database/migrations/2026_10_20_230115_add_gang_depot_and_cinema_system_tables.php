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
        // 1. Gang Depot enhancements: masks column
        Schema::table('gangs', function (Blueprint $table): void {
            if (! Schema::hasColumn('gangs', 'masks')) {
                $table->integer('masks')->default(0)->after('treasury');
            }
        });

        // 2. Gang Depot Items
        if (! Schema::hasTable('gang_items')) {
            Schema::create('gang_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('gang_id')->constrained('gangs')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();

                $table->index(['gang_id', 'item_id']);
            });
        }

        // 3. Cinema / Watchparty Rooms
        if (! Schema::hasTable('cinema_rooms')) {
            Schema::create('cinema_rooms', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('host_character_id')->constrained('characters')->cascadeOnDelete();
                $table->string('youtube_url');
                $table->string('youtube_video_id', 32);
                $table->boolean('is_official')->default(false);
                $table->boolean('is_private')->default(false);
                $table->string('passcode')->nullable();
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamps();
            });
        }

        // 4. Cinema Chat Messages & Reactions
        if (! Schema::hasTable('cinema_messages')) {
            Schema::create('cinema_messages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('room_id')->constrained('cinema_rooms')->cascadeOnDelete();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->text('message')->nullable();
                $table->string('gif_url', 500)->nullable();
                $table->string('sticker_key', 64)->nullable();
                $table->timestamps();

                $table->index(['room_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinema_messages');
        Schema::dropIfExists('cinema_rooms');
        Schema::dropIfExists('gang_items');

        Schema::table('gangs', function (Blueprint $table): void {
            if (Schema::hasColumn('gangs', 'masks')) {
                $table->dropColumn('masks');
            }
        });
    }
};
