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
        // 1. Smokkel Droppings (Tennisbal & Drones)
        if (! Schema::hasTable('smuggle_droppings')) {
            Schema::create('smuggle_droppings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // 'tennisball', 'drone'
                $table->string('loot_summary');
                $table->integer('cash_gained')->default(0);
                $table->integer('xp_gained')->default(0);
                $table->boolean('caught')->default(false);
                $table->timestamps();

                $table->index(['character_id', 'created_at']);
            });
        }

        // 2. TBS FM Shoutouts (Afdelingsradio)
        if (! Schema::hasTable('radio_shoutouts')) {
            Schema::create('radio_shoutouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained()->cascadeOnDelete();
                $table->string('character_name');
                $table->string('gang_name')->nullable();
                $table->boolean('is_anonymous')->default(false);
                $table->string('track_title');
                $table->text('message');
                $table->unsignedInteger('likes_count')->default(0);
                $table->timestamps();

                $table->index('created_at');
            });
        }

        // Radio shoutout likes to prevent duplicate likes
        if (! Schema::hasTable('radio_shoutout_likes')) {
            Schema::create('radio_shoutout_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('radio_shoutout_id')->constrained('radio_shoutouts')->cascadeOnDelete();
                $table->foreignId('character_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['radio_shoutout_id', 'character_id']);
            });
        }

        // 3. Het Grote Uitbraakcomplot (Escape Tunnel)
        if (! Schema::hasTable('escape_tunnels')) {
            Schema::create('escape_tunnels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->unique()->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('stage')->default(1);
                $table->unsignedTinyInteger('progress_pct')->default(0);
                $table->unsignedInteger('sand_in_socks')->default(0); // max 100 before dumping
                $table->unsignedInteger('spoons_used')->default(0);
                $table->unsignedInteger('wood_planks')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamp('escaped_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escape_tunnels');
        Schema::dropIfExists('radio_shoutout_likes');
        Schema::dropIfExists('radio_shoutouts');
        Schema::dropIfExists('smuggle_droppings');
    }
};
