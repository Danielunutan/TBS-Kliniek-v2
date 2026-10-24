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
        Schema::create('profile_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('visited_id')->constrained('characters')->cascadeOnDelete();
            $table->timestamp('visited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['visitor_id', 'visited_id']);
            $table->index(['visited_id', 'visited_at']);
            $table->index(['visitor_id', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_visits');
    }
};
