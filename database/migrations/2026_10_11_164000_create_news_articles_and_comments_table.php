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
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('emoji', 50)->default('📰');
            $table->string('image_url')->nullable();
            $table->longText('content');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('news_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_article_id')->constrained('news_articles')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_comments');
        Schema::dropIfExists('news_articles');
    }
};
