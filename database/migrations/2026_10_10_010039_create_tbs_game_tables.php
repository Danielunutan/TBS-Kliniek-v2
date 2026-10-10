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
        Schema::create('gangs', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('tag', 10);
            $table->unsignedBigInteger('leader_character_id')->nullable();
            $table->text('description')->nullable();
            $table->string('specialty');
            $table->string('emblem')->default('fa-shield-halved');
            $table->bigInteger('treasury')->default(0);
            $table->timestamps();
        });

        Schema::create('characters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('title')->default('Nieuwe Patiënt');
            $table->string('avatar')->default('characters/avatar-default.png');
            $table->text('bio')->nullable();
            $table->string('status_message')->default('Pas binnengebracht in de TBS Kliniek...');

            // Progression
            $table->unsignedInteger('level')->default(1);
            $table->unsignedBigInteger('xp')->default(0);
            $table->unsignedBigInteger('total_xp')->default(0);

            // Currencies (KliniekCash)
            $table->bigInteger('cash')->default(250);
            $table->bigInteger('bank')->default(1000);

            // Energy & Health
            $table->integer('energy')->default(100);
            $table->integer('max_energy')->default(100);
            $table->timestamp('last_energy_refill_at')->useCurrent();
            $table->integer('health')->default(100);
            $table->integer('max_health')->default(100);
            $table->unsignedInteger('respect')->default(10);

            // RPG Attributes
            $table->unsignedInteger('strength')->default(10);
            $table->unsignedInteger('defense')->default(10);
            $table->unsignedInteger('stamina')->default(10);
            $table->unsignedInteger('intelligence')->default(10);
            $table->unsignedInteger('charisma')->default(10);
            $table->unsignedInteger('discipline')->default(10);

            // Statistics
            $table->unsignedInteger('fights_won')->default(0);
            $table->unsignedInteger('fights_lost')->default(0);
            $table->unsignedInteger('thefts_succeeded')->default(0);
            $table->unsignedInteger('thefts_failed')->default(0);
            $table->unsignedInteger('missions_completed')->default(0);
            $table->unsignedInteger('prison_sentences')->default(0);
            $table->unsignedInteger('prison_escapes')->default(0);

            // Prison status
            $table->boolean('is_in_prison')->default(false);
            $table->timestamp('prison_until')->nullable();
            $table->string('prison_reason')->nullable();

            // Gang association
            $table->foreignId('gang_id')->nullable()->constrained('gangs')->nullOnDelete();
            $table->string('gang_role')->default('member');

            // Activity
            $table->timestamp('protection_until')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        Schema::create('missions', function (Blueprint $table): void {
            $table->id();
            $table->string('category')->default('beginners');
            $table->string('storyline')->nullable();
            $table->integer('story_step')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('giver_name')->default('Henkie de Frikandelkoning');
            $table->string('location_name')->default('De Kantine');
            $table->unsignedInteger('required_level')->default(1);
            $table->unsignedInteger('energy_cost')->default(10);
            $table->unsignedInteger('success_rate')->default(85);
            $table->unsignedInteger('min_cash_reward')->default(20);
            $table->unsignedInteger('max_cash_reward')->default(50);
            $table->unsignedInteger('xp_reward')->default(25);
            $table->unsignedInteger('respect_reward')->default(2);
            $table->text('fail_message')->nullable();
            $table->text('success_message')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('character_missions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('completed_count')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();
            $table->unique(['character_id', 'mission_id']);
        });

        Schema::create('guestbook_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('author_character_id')->constrained('characters')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('game_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('type')->default('info');
            $table->string('icon')->default('fa-bell');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('theft_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thief_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('victim_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('target_name');
            $table->boolean('success');
            $table->unsignedBigInteger('cash_stolen')->default(0);
            $table->unsignedInteger('xp_gained')->default(0);
            $table->integer('respect_gained')->default(0);
            $table->unsignedInteger('energy_spent')->default(0);
            $table->boolean('jailed')->default(false);
            $table->unsignedInteger('jail_minutes')->default(0);
            $table->text('log_message');
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('category')->default('consumable'); // consumable, equipment, collectible
            $table->string('rarity')->default('common'); // common, uncommon, rare, epic, legendary
            $table->unsignedBigInteger('price_kc')->default(50);
            $table->integer('energy_restore')->default(0);
            $table->integer('health_restore')->default(0);
            $table->integer('strength_bonus')->default(0);
            $table->integer('defense_bonus')->default(0);
            $table->integer('stamina_bonus')->default(0);
            $table->integer('intelligence_bonus')->default(0);
            $table->integer('charisma_bonus')->default(0);
            $table->integer('discipline_bonus')->default(0);
            $table->string('icon')->default('fa-box');
            $table->boolean('is_buyable')->default(true);
            $table->timestamps();
        });

        Schema::create('character_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_equipped')->default(false);
            $table->timestamps();
        });

        Schema::create('training_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('stat');
            $table->unsignedInteger('stat_gain')->default(1);
            $table->unsignedInteger('energy_cost')->default(10);
            $table->unsignedBigInteger('cash_cost')->default(50);
            $table->string('trainer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_logs');
        Schema::dropIfExists('character_items');
        Schema::dropIfExists('items');
        Schema::dropIfExists('theft_records');
        Schema::dropIfExists('game_notifications');
        Schema::dropIfExists('guestbook_entries');
        Schema::dropIfExists('character_missions');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('characters');
        Schema::dropIfExists('gangs');
    }
};
