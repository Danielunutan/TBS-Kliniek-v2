<?php

use App\Models\DirectMessage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert any migrated automated friend request messages that had a player sender_id
        // to official Kliniek Directie notifications (sender_id = null, type = 'system')
        DirectMessage::whereNotNull('sender_id')
            ->where(function ($q) {
                $q->where('message', 'like', '%vriendschapsverzoek%')
                    ->orWhere('message', 'like', '%heeft je een vriendschapsverzoek gestuurd%')
                    ->orWhere('message', 'like', '%heeft je vriendschapsverzoek zojuist geaccepteerd%');
            })
            ->update([
                'sender_id' => null,
                'type' => 'system',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
