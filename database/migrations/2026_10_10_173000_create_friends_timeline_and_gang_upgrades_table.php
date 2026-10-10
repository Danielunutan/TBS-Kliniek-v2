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
        Schema::create('friends', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained('characters')->cascadeOnDelete();
            $table->boolean('is_bff')->default(false);
            $table->timestamp('last_respected_at')->nullable();
            $table->string('status')->default('accepted');
            $table->timestamps();

            $table->unique(['character_id', 'friend_id']);
        });

        Schema::create('timeline_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->string('content', 300);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
        });

        Schema::table('gangs', function (Blueprint $table): void {
            if (! Schema::hasColumn('gangs', 'bling_shops')) {
                $table->integer('bling_shops')->default(0);
            }
            if (! Schema::hasColumn('gangs', 'protection_points')) {
                $table->bigInteger('protection_points')->default(1000);
            }
            if (! Schema::hasColumn('gangs', 'attack_power')) {
                $table->bigInteger('attack_power')->default(500);
            }
            if (! Schema::hasColumn('gangs', 'max_members')) {
                $table->integer('max_members')->default(10);
            }
            if (! Schema::hasColumn('gangs', 'personal_shopper')) {
                $table->string('personal_shopper')->nullable();
            }
            if (! Schema::hasColumn('gangs', 'money_magnet')) {
                $table->string('money_magnet')->nullable();
            }
            if (! Schema::hasColumn('gangs', 'gang_stars')) {
                $table->integer('gang_stars')->default(0);
            }
        });

        Schema::table('characters', function (Blueprint $table): void {
            if (! Schema::hasColumn('characters', 'last_friends_mass_message_at')) {
                $table->timestamp('last_friends_mass_message_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table): void {
            $table->dropColumn(['last_friends_mass_message_at']);
        });

        Schema::table('gangs', function (Blueprint $table): void {
            $table->dropColumn([
                'bling_shops',
                'protection_points',
                'attack_power',
                'max_members',
                'personal_shopper',
                'money_magnet',
                'gang_stars',
            ]);
        });

        Schema::dropIfExists('timeline_posts');
        Schema::dropIfExists('friends');
    }
};
