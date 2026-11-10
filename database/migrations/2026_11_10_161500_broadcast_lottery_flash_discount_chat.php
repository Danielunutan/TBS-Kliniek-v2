<?php

use App\Models\Character;
use App\Models\ChatMessage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $authorChar = Character::where('name', 'Daniël')
            ->orWhere('name', 'Daniel')
            ->first() ?? Character::first();

        if (Schema::hasTable('chat_messages') && $authorChar) {
            ChatMessage::create([
                'character_id' => $authorChar->id,
                'message' => '🎟️ MEGA LOTERIJ ACTIE! 50% korting op alle loten is NU actief én 100% van alle inleg stroomt rechtstreeks de jackpot pot in! Pak je kans en speel voordelig mee!',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

