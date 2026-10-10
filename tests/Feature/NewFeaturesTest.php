<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_gang_management_and_treasury(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $leader = $user->character;
        $leader->update(['cash' => 10000, 'stars' => 300, 'gang_id' => null, 'gang_role' => 'none']);

        $this->actingAs($user);

        // 1. Create gang
        $response = $this->post(route('gangs.create'), [
            'name' => 'De Meesterbreinen',
            'tag' => 'DMB',
            'specialty' => 'Kantine Controle',
            'description' => 'Controle over de gehele oostvleugel.',
        ]);
        $response->assertSessionHas('success');

        $leader->refresh();
        $this->assertNotNull($leader->gang_id);
        $this->assertEquals('leader', $leader->gang_role);

        $gang = $leader->gang;

        // 2. Update settings
        $settingsResponse = $this->post(route('gangs.settings'), [
            'motd' => 'Niemand raakt onze kantine aan!',
            'specialty' => 'Smokkel & Handel',
            'description' => 'Geüpdatete bende beschrijving.',
            'emblem' => 'fa-skull',
            'is_open' => true,
        ]);
        $settingsResponse->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals('Niemand raakt onze kantine aan!', $gang->motd);
        $this->assertEquals('fa-skull', $gang->emblem);

        // 3. Donate to treasury
        $donateResponse = $this->post(route('gangs.donate'), ['amount' => 500]);
        $donateResponse->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals(1500, $gang->treasury); // 1000 base + 500

        // 4. Withdraw from treasury (Leader)
        $withdrawResponse = $this->post(route('gangs.withdraw'), ['amount' => 400]);
        $withdrawResponse->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals(1100, $gang->treasury);
    }

    public function test_marketplace_trading_stars_and_bags(): void
    {
        $seller = User::where('email', 'demo@tbskliniek.nl')->first();
        $sellerChar = $seller->character;
        $sellerChar->update(['stars' => 10, 'cash' => 1000, 'bank' => 2000]);

        $this->actingAs($seller);

        // 1. Place listing
        $listingResponse = $this->post(route('marketplace.store'), [
            'item_type' => 'stars',
            'amount' => 3,
            'price' => 1500,
            'currency' => 'cash',
        ]);
        $listingResponse->assertSessionHas('success');

        $sellerChar->refresh();
        $this->assertEquals(7, $sellerChar->stars); // 10 - 3 escrowed

        $listing = MarketplaceListing::latest()->first();
        $this->assertNotNull($listing);
        $this->assertEquals('stars', $listing->item_type);

        // 2. Buyer buys listing
        $buyerUser = User::create([
            'name' => 'Koper',
            'email' => 'koper@tbskliniek.nl',
            'password' => bcrypt('password'),
        ]);
        $buyerChar = Character::create([
            'user_id' => $buyerUser->id,
            'name' => 'Rijke_Koper',
            'title' => 'Patiënt',
            'avatar' => 'characters/henkie.jpg',
            'gender' => 'male',
            'level' => 3,
            'cash' => 2000,
            'bank' => 0,
            'stars' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 5,
            'defense' => 5,
            'speed' => 5,
            'intellect' => 5,
            'respect' => 5,
        ]);

        $this->actingAs($buyerUser);
        $buyResponse = $this->post(route('marketplace.buy', $listing->id));
        $buyResponse->assertSessionHas('success');

        $buyerChar->refresh();
        $sellerChar->refresh();
        $this->assertEquals(3, $buyerChar->stars);
        $this->assertEquals(500, $buyerChar->cash); // 2000 - 1500
        $this->assertEquals(3500, $sellerChar->bank); // Clean money on bank: 2000 + 1500!
    }

    public function test_prison_stars_buyout_and_breakout(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(15),
            'stars' => 5,
        ]);

        $this->actingAs($user);

        // Buy self out with 2 stars
        $buyoutResponse = $this->post(route('prison.buyout'));
        $buyoutResponse->assertSessionHas('success');

        $char->refresh();
        $this->assertFalse($char->isImprisoned());
        $this->assertEquals(3, $char->stars); // 5 - 2
    }

    public function test_production_and_hustle_cooldowns_and_stars_skip(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => false,
            'energy' => 100,
            'stars' => 10,
        ]);

        $this->actingAs($user);

        // 1. First hustle succeeds or attempts and sets 3 min cooldown
        $res1 = $this->post(route('hustle.attempt'), ['hustle_id' => 1]);
        $this->assertTrue($char->isOnCooldown('hustle'));

        // 2. Second hustle is blocked by cooldown
        $res2 = $this->post(route('hustle.attempt'), ['hustle_id' => 1]);
        $res2->assertSessionHas('error');

        // 3. Skip hustle cooldown with 1 star
        $skipRes = $this->post(route('cooldown.skip'), ['action' => 'hustle']);
        $skipRes->assertSessionHas('success');
        $char->refresh();
        $this->assertFalse($char->isOnCooldown('hustle'));
        $this->assertEquals(9, $char->stars); // 10 - 1

        // 4. Production cooldown (produce sigaretten -> 5 min cooldown)
        $prodRes1 = $this->post(route('smuggle.produce'), ['type' => 'sigaretten']);
        $prodRes1->assertSessionHas('success');
        $this->assertTrue($char->isOnCooldown('produce_sigaretten'));

        // Second produce is blocked
        $prodRes2 = $this->post(route('smuggle.produce'), ['type' => 'sigaretten']);
        $prodRes2->assertSessionHas('error');

        // Skip production cooldown with 1 star
        $skipProdRes = $this->post(route('cooldown.skip'), ['action' => 'produce_sigaretten']);
        $skipProdRes->assertSessionHas('success');
        $char->refresh();
        $this->assertFalse($char->isOnCooldown('produce_sigaretten'));
        $this->assertEquals(8, $char->stars); // 9 - 1
    }

    public function test_medicijn_kluis_batch_redemption(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['bank' => 0, 'stars' => 0, 'masks' => 0, 'respect' => 10]);

        $this->actingAs($user);

        // Create 2 test vouchers
        VaultCode::create([
            'code' => 'TESTCODE1',
            'reward_cash' => 5000,
            'reward_stars' => 2,
            'reward_tokens' => 1,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        VaultCode::create([
            'code' => 'TESTCODE2',
            'reward_cash' => 3000,
            'reward_stars' => 1,
            'reward_tokens' => 1,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        // Redeem both at once with newline separation
        $response = $this->post(route('vault.redeem'), [
            'codes' => "TESTCODE1\nTESTCODE2",
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertEquals(8000, $char->bank); // deposited to bank
        $this->assertEquals(3, $char->stars);
        $this->assertEquals(2, $char->masks); // 1 mask per cracked code
        $this->assertEquals(20, $char->respect); // +5 per code
    }

    public function test_vault_code_single_click_redemption_by_code_id(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['bank' => 0, 'stars' => 0, 'masks' => 0]);

        $this->actingAs($user);

        $code = VaultCode::create([
            'code' => 'CLICKME123',
            'reward_cash' => 4500,
            'reward_stars' => 3,
            'reward_tokens' => 2,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        $response = $this->post(route('vault.redeem'), [
            'code_id' => $code->id,
        ]);

        $response->assertSessionHas('success');
        $char->refresh();
        $code->refresh();

        $this->assertTrue($code->is_redeemed);
        $this->assertEquals(4500, $char->bank);
        $this->assertEquals(3, $char->stars);
        $this->assertEquals(1, $char->masks);
    }

    public function test_vault_code_batch_click_redemption_by_redeem_all(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['bank' => 0, 'stars' => 0, 'masks' => 0]);

        $this->actingAs($user);

        $c1 = VaultCode::create([
            'code' => 'BATCH1',
            'reward_cash' => 2000,
            'reward_stars' => 1,
            'reward_tokens' => 2,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        $c2 = VaultCode::create([
            'code' => 'BATCH2',
            'reward_cash' => 3000,
            'reward_stars' => 2,
            'reward_tokens' => 2,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        $response = $this->post(route('vault.redeem'), [
            'redeem_all' => 1,
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertTrue($c1->fresh()->is_redeemed);
        $this->assertTrue($c2->fresh()->is_redeemed);
        $this->assertEquals(5000, $char->bank);
        $this->assertEquals(3, $char->stars);
        $this->assertEquals(2, $char->masks);
    }

    public function test_zakkenrollen_requires_and_consumes_mask(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['masks' => 0, 'energy' => 50]);

        $this->actingAs($user);

        // Without masks, should fail
        $res1 = $this->post(route('pickpocket.steal'), ['action_id' => 1]);
        $res1->assertSessionHas('error');

        // Give 2 masks
        $char->update(['masks' => 2]);

        $res2 = $this->post(route('pickpocket.steal'), ['action_id' => 1]);
        $char->refresh();
        $this->assertEquals(1, $char->masks); // 1 mask consumed
    }

    public function test_star_shop_purchases(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['stars' => 250, 'masks' => 0, 'unopened_bags' => 0]);

        $this->actingAs($user);

        // Buy 1x mask for 15 stars
        $this->post(route('shop.buy-star-item'), ['item_type' => 'mask_1'])
            ->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals(1, $char->masks);
        $this->assertEquals(235, $char->stars);

        // Buy 150 star package
        $this->post(route('shop.buy-star-item'), ['item_type' => 'package_150'])
            ->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals(1, $char->unopened_bags);
        $this->assertEquals(85, $char->stars);
    }

    public function test_high_star_bundle_purchase_with_bonus(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['stars' => 500, 'unopened_bags' => 0, 'bank' => 0, 'masks' => 0]);

        $this->actingAs($user);

        // Buy Big Bucks Bundle (250 stars)
        $response = $this->post(route('shop.buy-bundle'), [
            'bundle_id' => 'big_bucks',
            'quantity' => 1,
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertEquals(250, $char->stars); // 500 - 250
        $this->assertEquals(1500000, $char->bank);
        $this->assertEquals(11, $char->unopened_bags); // 10 koffers + 1 bonus XXL koffer (>= 100 stars)
        $this->assertEquals(40, $char->masks);
    }

    public function test_gang_mass_message_and_alliances(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $leader = $user->character;
        $leader->update(['stars' => 300, 'gang_id' => null, 'gang_role' => 'none']);

        $this->actingAs($user);

        // Create gang
        $this->post(route('gangs.create'), [
            'name' => 'Baddie Alliance Gang',
            'tag' => 'BAG',
            'specialty' => 'Moord & Slay',
        ]);

        $leader->refresh();
        $gang = $leader->gang;

        // Send mass message
        $msgRes = $this->post(route('gangs.mass-message'), [
            'subject' => 'Strijd Om De Stack!',
            'message' => 'Iedereen verzamelen voor de koffer jacht!',
        ]);
        $msgRes->assertSessionHas('success');

        // Other gang for alliance
        $otherGang = Gang::create([
            'name' => 'Sisterhood',
            'tag' => 'SH',
            'specialty' => 'Handel',
        ]);

        $allyRes = $this->post(route('gangs.add-alliance'), [
            'ally_gang_id' => $otherGang->id,
        ]);
        $allyRes->assertSessionHas('success');
        $this->assertEquals(1, $gang->alliances()->count());
    }
}
