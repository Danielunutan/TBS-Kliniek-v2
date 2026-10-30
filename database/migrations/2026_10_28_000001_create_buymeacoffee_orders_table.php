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
        if (Schema::hasTable('buymeacoffee_orders')) {
            return;
        }

        Schema::create('buymeacoffee_orders', function (Blueprint $table) {
            $table->id();
            $table->string('event')->default('donation');
            $table->string('external_id')->nullable()->index();
            $table->string('supporter_name')->nullable()->index();
            $table->string('supporter_email')->nullable()->index();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->string('membership_tier')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('status')->default('unmatched')->index(); // unmatched, matched, manually_linked, cancelled
            $table->boolean('is_membership')->default(false)->index();
            $table->string('membership_status')->nullable(); // active, cancelled, paused
            $table->json('payload')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buymeacoffee_orders');
    }
};
