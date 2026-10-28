<?php

namespace Tests\Feature;

use App\Models\BuyMeACoffeeOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyMeACoffeeAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_webhook_stores_unmatched_order_when_tag_is_missing(): void
    {
        $response = $this->postJson(route('webhooks.buymeacoffee'), [
            'event' => 'membership.started',
            'data' => [
                'subscription_id' => 'sub_12345',
                'supporter_name' => 'Anonieme Donateur',
                'supporter_email' => 'onbekend@example.com',
                'amount' => 14.0,
                'membership_tier_name' => 'TBS Gangster Elite',
                'support_message' => 'Geweldig spel ga zo door!',
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('buymeacoffee_orders', [
            'external_id' => 'sub_12345',
            'supporter_name' => 'Anonieme Donateur',
            'status' => 'unmatched',
            'is_membership' => true,
        ]);
    }

    public function test_admin_can_manually_link_order_to_character(): void
    {
        $adminUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $adminUser->role = 'admin';
        $adminUser->is_staff = true;
        $adminUser->save();

        $character = $adminUser->character;
        $character->is_vip = false;
        $character->vip_expires_at = null;
        $character->stars = 0;
        $character->save();

        $order = BuyMeACoffeeOrder::create([
            'event' => 'membership.started',
            'external_id' => 'sub_unmatched_99',
            'supporter_name' => 'Kees',
            'supporter_email' => 'kees@example.com',
            'amount' => 6.0,
            'membership_tier' => 'TBS V.I.P. Lid',
            'status' => 'unmatched',
            'is_membership' => true,
        ]);

        $response = $this->actingAs($adminUser)->post(route('admin.buymeacoffee.link', $order), [
            'character_id' => $character->id,
            'membership_tier' => 'TBS V.I.P. Lid',
            'grant_rewards' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('manually_linked', $order->status);
        $this->assertEquals($character->id, $order->character_id);

        $character->refresh();
        $this->assertTrue($character->isVip());
        $this->assertNotNull($character->vip_expires_at);
        $this->assertGreaterThan(0, $character->stars);
    }

    public function test_webhook_cancellation_revokes_character_vip(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->is_vip = true;
        $character->vip_expires_at = now()->addDays(20);
        $character->save();

        BuyMeACoffeeOrder::create([
            'event' => 'membership.started',
            'external_id' => 'sub_active_777',
            'supporter_name' => 'Active Supporter',
            'supporter_email' => 'active@example.com',
            'amount' => 24.0,
            'membership_tier' => 'TBS Kliniek Baas',
            'character_id' => $character->id,
            'status' => 'matched',
            'is_membership' => true,
            'membership_status' => 'active',
        ]);

        $response = $this->postJson(route('webhooks.buymeacoffee'), [
            'event' => 'membership.cancelled',
            'data' => [
                'subscription_id' => 'sub_active_777',
                'supporter_name' => 'Active Supporter',
                'supporter_email' => 'active@example.com',
            ],
        ]);

        $response->assertStatus(200);

        $character->refresh();
        $this->assertFalse($character->isVip());

        $this->assertDatabaseHas('buymeacoffee_orders', [
            'external_id' => 'sub_active_777',
            'status' => 'cancelled',
            'membership_status' => 'cancelled',
        ]);
    }
}
