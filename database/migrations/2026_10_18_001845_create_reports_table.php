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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('characters')->cascadeOnDelete();
            $table->string('reported_type'); // 'character', 'gang', 'chat_message'
            $table->unsignedBigInteger('reported_id');
            $table->string('reported_name')->nullable();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'resolved', 'dismissed'
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('mod_notes')->nullable();
            $table->timestamps();

            $table->index(['reported_type', 'reported_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
