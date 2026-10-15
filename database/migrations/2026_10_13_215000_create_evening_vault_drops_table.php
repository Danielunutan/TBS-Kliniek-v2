<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evening_vault_drops')) {
            Schema::create('evening_vault_drops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
                $table->date('drop_date');
                $table->timestamps();

                $table->unique(['character_id', 'drop_date']);
                $table->index('drop_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evening_vault_drops');
    }
};
