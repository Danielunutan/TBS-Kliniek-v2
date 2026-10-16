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
        if (! Schema::hasTable('timeline_comments')) {
            Schema::create('timeline_comments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('timeline_post_id')->constrained('timeline_posts')->cascadeOnDelete();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->string('content', 300);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('timeline_likes')) {
            Schema::create('timeline_likes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('timeline_post_id')->constrained('timeline_posts')->cascadeOnDelete();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->string('emoji', 20)->default('❤️');
                $table->timestamps();

                $table->unique(['timeline_post_id', 'character_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_likes');
        Schema::dropIfExists('timeline_comments');
    }
};
