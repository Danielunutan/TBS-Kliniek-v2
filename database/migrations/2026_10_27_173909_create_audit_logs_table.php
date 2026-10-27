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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->foreignId('character_id')->nullable()->index();
            $table->string('action', 64)->index();
            $table->string('category', 64)->index();
            $table->text('description');
            $table->bigInteger('amount')->nullable();
            $table->string('risk_level', 32)->default('normaal')->index();
            $table->integer('risk_score')->default(0);
            $table->string('risk_reason')->nullable();
            $table->json('risk_flags')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['risk_level', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['character_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
