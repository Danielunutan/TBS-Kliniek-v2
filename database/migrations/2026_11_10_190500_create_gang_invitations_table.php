<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gang_invitations')) {
            Schema::create('gang_invitations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
                $table->foreignId('character_id')->constrained()->cascadeOnDelete();
                $table->foreignId('invited_by_id')->constrained('characters')->cascadeOnDelete();
                $table->string('message')->nullable();
                $table->string('status')->default('pending'); // pending, accepted, declined, cancelled
                $table->timestamps();

                $table->index(['gang_id', 'status']);
                $table->index(['character_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gang_invitations');
    }
};
