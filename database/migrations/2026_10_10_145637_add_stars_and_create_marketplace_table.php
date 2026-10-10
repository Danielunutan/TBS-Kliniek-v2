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
        Schema::table('characters', function (Blueprint $table) {
            if (! Schema::hasColumn('characters', 'stars')) {
                $table->integer('stars')->default(5)->after('vault_tokens');
            }
            if (! Schema::hasColumn('characters', 'unopened_bags')) {
                $table->integer('unopened_bags')->default(1)->after('stars');
            }
        });

        Schema::table('gangs', function (Blueprint $table) {
            if (! Schema::hasColumn('gangs', 'is_open')) {
                $table->boolean('is_open')->default(true)->after('treasury');
            }
            if (! Schema::hasColumn('gangs', 'motd')) {
                $table->string('motd')->nullable()->after('is_open');
            }
        });

        if (! Schema::hasTable('marketplace_listings')) {
            Schema::create('marketplace_listings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('characters')->cascadeOnDelete();
                $table->string('item_type'); // 'stars', 'vault_codes', 'packages', 'cash'
                $table->string('title');
                $table->integer('amount')->default(1);
                $table->integer('price')->default(100);
                $table->string('currency')->default('cash'); // 'cash' or 'stars'
                $table->boolean('is_sold')->default(false);
                $table->foreignId('buyer_id')->nullable()->constrained('characters')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('vault_codes')) {
            Schema::create('vault_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->integer('reward_cash')->default(1000);
                $table->integer('reward_stars')->default(2);
                $table->integer('reward_tokens')->default(3);
                $table->foreignId('character_id')->nullable()->constrained('characters')->nullOnDelete();
                $table->boolean('is_redeemed')->default(false);
                $table->foreignId('redeemed_by_id')->nullable()->constrained('characters')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_codes');
        Schema::dropIfExists('marketplace_listings');

        Schema::table('gangs', function (Blueprint $table) {
            $table->dropColumn(['is_open', 'motd']);
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['stars', 'unopened_bags']);
        });
    }
};
