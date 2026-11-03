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

    public function test_webhook_with_configured_secret_rejects_request_without_signature(): void
    {
        config(['services.buymeacoffee.webhook_secret' => 'unit-test-signing-secret']);

        $response = $this->postJson(route('webhooks.buymeacoffee'), [
            'event' => 'membership.started',
            'data' => [
                'subscription_id' => 'sub_forged_1',
                'supporter_name' => 'Demo Speler',
                'membership_tier_name' => 'VIP Lifetime',
            ],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('buymeacoffee_orders', ['external_id' => 'sub_forged_1']);
    }

    public function test_webhook_with_configured_secret_rejects_wrong_signature(): void
    {
        config(['services.buymeacoffee.webhook_secret' => 'unit-test-signing-secret']);

        $response = $this->postJson(route('webhooks.buymeacoffee'), [
            'event' => 'membership.started',
            'data' => ['subscription_id' => 'sub_forged_2'],
        ], ['X-Signature-Sha256' => 'deadbeef']);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('buymeacoffee_orders', ['external_id' => 'sub_forged_2']);
    }

    public function test_webhook_with_configured_secret_accepts_valid_signature(): void
    {
        $secret = 'unit-test-signing-secret';
        config(['services.buymeacoffee.webhook_secret' => $secret]);

        $payload = [
            'event' => 'membership.started',
            'data' => [
                'subscription_id' => 'sub_signed_1',
                'supporter_name' => 'Anonieme Donateur',
                'membership_tier_name' => 'TBS Gangster Elite',
            ],
        ];
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $response = $this->call(
            'POST',
            route('webhooks.buymeacoffee'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE_SHA256' => $signature, 'HTTP_ACCEPT' => 'application/json'],
            json_encode($payload)
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('buymeacoffee_orders', ['external_id' => 'sub_signed_1']);
    }

    public function test_admin_regrant_safely_prevents_duplicate_lifetime_vip_rewards(): void
    {
        $adminUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $adminUser->role = 'admin';
        $adminUser->is_staff = true;
        $adminUser->save();

        $character = $adminUser->character;
        $character->is_vip = true;
        $character->vip_expires_at = now()->addYears(99);
        $character->stars = 1500;
        $character->masks = 30;
        $character->xxl_cases = 10;
        $character->perks = [
            'supporter_tier' => 4,
            'hourly_cash_bonus' => 50000,
        ];
        $character->save();

        $order = BuyMeACoffeeOrder::create([
            'event' => 'membership.manual',
            'external_id' => 'manual_lifetime_test',
            'supporter_name' => $character->name,
            'amount' => 125.0,
            'membership_tier' => 'VIP LIFETIME',
            'character_id' => $character->id,
            'status' => 'manually_linked',
            'is_membership' => true,
            'payload' => [
                'rewards_granted' => true,
            ],
        ]);

        $response = $this->actingAs($adminUser)->post(route('admin.buymeacoffee.regrant', $order));

        $response->assertRedirect();
        $response->assertSessionHas('info');

        $character->refresh();
        $this->assertEquals(1500, $character->stars);
        $this->assertEquals(30, $character->masks);
        $this->assertEquals(10, $character->xxl_cases);
    }

    public function test_admin_manual_order_prevents_rapid_duplicate_double_click(): void
    {
        $adminUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $adminUser->role = 'admin';
        $adminUser->is_staff = true;
        $adminUser->save();

        $character = $adminUser->character;

        // First manual order submission
        $response1 = $this->actingAs($adminUser)->post(route('admin.buymeacoffee.manual'), [
            'supporter_name' => 'Demo Donateur',
            'supporter_email' => 'donateur@example.com',
            'character_id' => $character->id,
            'amount' => 6.00,
            'membership_tier' => 'TBS V.I.P. Lid',
            'is_membership' => 1,
            'message' => 'Eerste donatie',
        ]);

        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        // Immediate duplicate submission (simulating double click)
        $response2 = $this->actingAs($adminUser)->post(route('admin.buymeacoffee.manual'), [
            'supporter_name' => 'Demo Donateur',
            'supporter_email' => 'donateur@example.com',
            'character_id' => $character->id,
            'amount' => 6.00,
            'membership_tier' => 'TBS V.I.P. Lid',
            'is_membership' => 1,
            'message' => 'Eerste donatie',
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('warning');

        $this->assertEquals(1, BuyMeACoffeeOrder::where('character_id', $character->id)->where('membership_tier', 'TBS V.I.P. Lid')->count());
    }

    public function test_lifetime_vip_is_not_downgraded_by_smaller_tier(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->is_vip = true;
        $character->vip_expires_at = now()->addYears(90);
        $character->perks = [
            'supporter_tier' => 4,
            'hourly_cash_bonus' => 50000,
            'is_donator' => true,
        ];
        $character->save();

        // Process a webhook for a smaller tier (Tier 1) for this character
        $response = $this->postJson(route('webhooks.buymeacoffee'), [
            'event' => 'membership.started',
            'data' => [
                'subscription_id' => 'sub_tier1_check',
                'supporter_name' => 'Demo Supporter',
                'amount' => 6.0,
                'membership_tier_name' => 'TBS V.I.P. Lid (Tier 1)',
                'support_message' => "Donatie voor speler #{$character->id}",
            ],
        ]);

        $response->assertStatus(200);

        $character->refresh();
        // Lifetime VIP tier and bonus must NOT be downgraded
        $this->assertEquals(4, $character->perks['supporter_tier']);
        $this->assertEquals(50000, $character->perks['hourly_cash_bonus']);
        $this->assertTrue($character->isVip());
    }

    public function test_link_order_prevents_duplicate_rewards_when_already_granted(): void
    {
        $adminUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $adminUser->role = 'admin';
        $adminUser->is_staff = true;
        $adminUser->save();

        $character = $adminUser->character;
        $character->stars = 100;
        $character->save();

        $order = BuyMeACoffeeOrder::create([
            'event' => 'membership.started',
            'external_id' => 'sub_already_granted_99',
            'supporter_name' => 'Jan',
            'amount' => 6.0,
            'membership_tier' => 'TBS V.I.P. Lid',
            'character_id' => $character->id,
            'status' => 'manually_linked',
            'is_membership' => true,
            'payload' => [
                'rewards_granted' => true,
                'rewards_granted_at' => now()->subDay()->toIso8601String(),
            ],
        ]);

        // Re-linking the same order with grant_rewards = true
        $response = $this->actingAs($adminUser)->post(route('admin.buymeacoffee.link', $order), [
            'character_id' => $character->id,
            'membership_tier' => 'TBS V.I.P. Lid',
            'grant_rewards' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        // Stars should remain 100 and NOT get incremented again
        $this->assertEquals(100, $character->stars);
    }
}
