<?php

use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\GameNotification;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
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
        // 1. Convert vip_expires_at from 32-bit TIMESTAMP to DATETIME on MySQL to support dates past year 2038
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'vip_expires_at')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dateTime('vip_expires_at')->nullable()->change();
            });
        }

        // 2. Locate Syze and the VIP Lifetime order
        $character = Character::where('name', 'Syze')->first()
            ?? User::where('email', 'suzedirksen@hotmail.com')->first()?->character
            ?? BuyMeACoffeeOrder::where('id', 12)->first()?->character
            ?? BuyMeACoffeeOrder::where('supporter_name', 'Syze')->first()?->character;

        $order = BuyMeACoffeeOrder::where('id', 12)->first()
            ?? BuyMeACoffeeOrder::where('supporter_name', 'Syze')->where('membership_tier', 'VIP LIFETIME')->first();

        // 3. Grant VIP Lifetime perks idempotently (not double)
        if ($character) {
            $perks = $character->perks ?? [];
            $alreadyLifetimeVip = ($character->is_vip
                && (int) ($perks['supporter_tier'] ?? 0) === 4
                && $character->vip_expires_at
                && $character->vip_expires_at->year >= 2100);

            if (! $alreadyLifetimeVip) {
                $character->is_vip = true;
                $character->vip_expires_at = Carbon::now()->addYears(99);
                $character->stars = ($character->stars ?? 0) + 1500;
                $character->masks = ($character->masks ?? 0) + 30;
                $character->xxl_cases = ($character->xxl_cases ?? 0) + 10;
                $perks['supporter_tier'] = 4;
                $perks['has_custom_name_color'] = true;
                $perks['has_sparkle_effect'] = true;
                $perks['hourly_cash_bonus'] = 50000;
                $perks['is_donator'] = true;
                $character->perks = $perks;
                $character->save();

                GameNotification::create([
                    'character_id' => $character->id,
                    'type' => 'vip_membership',
                    'icon' => 'fa-crown',
                    'title' => 'Buy Me a Coffee: VIP LIFETIME Geactiveerd!',
                    'message' => "Gefeliciteerd! Je VIP LIFETIME membership is succesvol geactiveerd tot {$character->vip_expires_at->format('d-m-Y H:i')}. Geniet van je VIP privileges en beloningen!",
                    'is_read' => false,
                ]);

                try {
                    AuditLogService::log(
                        action: 'buymeacoffee_membership',
                        category: 'vip',
                        description: "BuyMeACoffee Membership geactiveerd (VIP LIFETIME) voor {$character->name} via herstelmigratie",
                        character: $character,
                        user: $character->user,
                        amount: 1500,
                        details: [
                            'order_id' => $order?->id ?? 12,
                            'source' => 'migration_recovery',
                        ]
                    );
                } catch (Throwable) {
                    // Silently continue if audit logging fails
                }
            }

            if ($order) {
                $order->character_id = $character->id;
                $order->status = 'manually_linked';
                $order->is_membership = true;
                $order->membership_status = 'active';
                $orderPayload = $order->payload ?? [];
                $orderPayload['rewards_granted'] = true;
                $orderPayload['rewards_granted_at'] = now()->toIso8601String();
                $order->payload = $orderPayload;
                $order->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting datetime to 32-bit timestamp would overflow any lifetime VIP expiry date past 2038.
    }
};
