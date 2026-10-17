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
        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('loan_number', 20)->index();
            $table->unsignedBigInteger('amount');
            $table->decimal('interest_rate', 5, 2)->default(8.50);
            $table->unsignedBigInteger('interest_amount');
            $table->unsignedBigInteger('total_due');
            $table->unsignedBigInteger('total_paid')->default(0);
            $table->unsignedBigInteger('remaining_due');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('started_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('source', 20)->default('bank');
            $table->bigInteger('amount');
            $table->unsignedBigInteger('balance_after')->default(0);
            $table->string('description', 255);
            $table->timestamps();

            $table->index(['character_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('loans');
    }
};
