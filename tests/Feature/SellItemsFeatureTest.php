<?php

namespace Tests\Feature;

use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellItemsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_player_can_sell_unopened_bags_directly(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->unopened_bags = 3;
        $character->bank = 1000;
        $character->save();

        $this->actingAs($user);

        $response = $this->post(route('packages.sell-bag'), [
            'amount' => 2,
        ]);

        $response->assertSessionHas('success');
        $character->refresh();

        $this->assertEquals(1, $character->unopened_bags);
        $this->assertEquals(6000, $character->bank); // 1000 + 2 * 2500
    }

    public function test_player_can_sell_vault_code_directly(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->bank = 2000;
        $character->save();

        $code = VaultCode::create([
            'code' => 'TEST-KLUIS-1234',
            'reward_cash' => 2500,
            'reward_stars' => 1,
            'reward_tokens' => 2,
            'character_id' => $character->id,
            'is_redeemed' => false,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('vault.sell-code'), [
            'code_id' => $code->id,
        ]);

        $response->assertSessionHas('success');
        $character->refresh();

        $this->assertEquals(5500, $character->bank); // 2000 + 3500
        $this->assertDatabaseMissing('vault_codes', ['id' => $code->id]);
    }

    public function test_player_can_sell_masks_directly(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->masks = 5;
        $character->bank = 500;
        $character->save();

        $this->actingAs($user);

        $response = $this->post(route('vault.sell-mask'), [
            'amount' => 3,
        ]);

        $response->assertSessionHas('success');
        $character->refresh();

        $this->assertEquals(2, $character->masks);
        $this->assertEquals(5000, $character->bank); // 500 + 3 * 1500
    }

    public function test_player_can_list_and_trade_masks_on_marketplace(): void
    {
        $sellerUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $seller = $sellerUser->character;
        $seller->masks = 10;
        $seller->bank = 0;
        $seller->save();

        $buyerUser = User::factory()->create();
        $buyer = $buyerUser->character()->create([
            'user_id' => $buyerUser->id,
            'name' => 'KoperPat',
            'cash' => 10000,
            'bank' => 0,
            'masks' => 0,
            'stars' => 0,
            'health' => 100,
            'energy' => 100,
        ]);

        $this->actingAs($sellerUser);

        // List 4 masks for 2000 cash
        $listResponse = $this->post(route('marketplace.store'), [
            'item_type' => 'masks',
            'amount' => 4,
            'price' => 2000,
            'currency' => 'cash',
        ]);

        $listResponse->assertSessionHas('success');
        $seller->refresh();
        $this->assertEquals(6, $seller->masks);

        $listing = MarketplaceListing::where('seller_id', $seller->id)->where('item_type', 'masks')->first();
        $this->assertNotNull($listing);

        // Buyer purchases the listing
        $this->actingAs($buyerUser);
        $buyResponse = $this->post(route('marketplace.buy', $listing->id));
        $buyResponse->assertSessionHas('success');

        $buyer->refresh();
        $seller->refresh();

        $this->assertEquals(4, $buyer->masks);
        $this->assertEquals(8000, $buyer->cash);
        $this->assertEquals(2000, $seller->bank); // clean money to seller bank
    }
}
