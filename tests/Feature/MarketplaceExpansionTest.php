<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\Item;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceExpansionTest extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = []): array
    {
        $user = User::factory()->create();
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate'.rand(1000, 9999),
            'cash' => 100000,
            'bank' => 50000,
            'stars' => 50,
            'level' => 5,
            'xp' => 100,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
        ], $attributes));

        return [$user, $character];
    }

    public function test_player_can_list_and_buy_depot_item(): void
    {
        [$sellerUser, $seller] = $this->createInmate(['cash' => 5000]);
        [$buyerUser, $buyer] = $this->createInmate(['cash' => 20000]);

        $item = Item::create([
            'name' => 'Steekmes Pro',
            'slug' => 'steekmes-pro',
            'category' => 'weapon',
            'description' => 'Scherp geslepen tandenborstel mes',
            'price_kc' => 1500,
            'rarity' => 'uncommon',
        ]);

        $charItem = CharacterItem::create([
            'character_id' => $seller->id,
            'item_id' => $item->id,
            'quantity' => 3,
            'is_equipped' => false,
        ]);

        // Seller lists 2x of the item
        $response = $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'depot_item',
            'character_item_id' => $charItem->id,
            'amount' => 2,
            'price' => 5000,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('success');

        // Check escrow deduction
        $charItem->refresh();
        $this->assertEquals(1, $charItem->quantity);

        $listing = MarketplaceListing::where('seller_id', $seller->id)->first();
        $this->assertNotNull($listing);
        $this->assertEquals('depot_item', $listing->item_type);
        $this->assertEquals($item->id, $listing->related_id);
        $this->assertEquals(2, $listing->amount);

        // Buyer buys the listing
        $buyResponse = $this->actingAs($buyerUser)->post(route('marketplace.buy', $listing->id));
        $buyResponse->assertSessionHas('success');

        // Check buyer received items and cash transferred
        $buyerItem = CharacterItem::where('character_id', $buyer->id)->where('item_id', $item->id)->first();
        $this->assertNotNull($buyerItem);
        $this->assertEquals(2, $buyerItem->quantity);

        $buyer->refresh();
        $seller->refresh();
        $this->assertEquals(15000, $buyer->cash); // 20000 - 5000
        $this->assertEquals(55000, $seller->bank); // 50000 + 5000
    }

    public function test_player_can_list_and_buy_garage_vehicle(): void
    {
        [$sellerUser, $seller] = $this->createInmate();
        [$buyerUser, $buyer] = $this->createInmate(['cash' => 50000]);

        $vehicle = Vehicle::create([
            'name' => 'Volkswagen Golf GTI',
            'slug' => 'vw-golf-gti',
            'category' => 'car',
            'speed_pct' => 140,
            'max_condition' => 100,
            'price_cash' => 25000,
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $seller->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 88,
            'is_active' => true,
        ]);

        // Seller lists vehicle for €12.500
        $response = $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'vehicle',
            'character_vehicle_id' => $charVehicle->id,
            'amount' => 1,
            'price' => 12500,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('success');

        // Check escrow
        $charVehicle->refresh();
        $this->assertTrue($charVehicle->is_for_sale);
        $this->assertFalse($charVehicle->is_active);

        $listing = MarketplaceListing::where('seller_id', $seller->id)->where('item_type', 'vehicle')->first();
        $this->assertNotNull($listing);

        // Buyer purchases vehicle
        $buyResponse = $this->actingAs($buyerUser)->post(route('marketplace.buy', $listing->id));
        $buyResponse->assertSessionHas('success');

        $charVehicle->refresh();
        $this->assertEquals($buyer->id, $charVehicle->character_id);
        $this->assertFalse($charVehicle->is_for_sale);

        $buyer->refresh();
        $this->assertEquals(37500, $buyer->cash); // 50000 - 12500
    }

    public function test_gang_officer_can_list_and_trade_bende_pand(): void
    {
        [$sellerUser, $seller] = $this->createInmate();
        [$buyerUser, $buyer] = $this->createInmate(['cash' => 200000]);

        $sellerGang = Gang::create([
            'name' => 'Red Vipers',
            'tag' => 'VIPER',
            'specialty' => 'smuggle',
            'leader_character_id' => $seller->id,
            'treasury' => 10000,
            'level' => 2,
        ]);
        $seller->update(['gang_id' => $sellerGang->id, 'gang_role' => 'leader']);

        $buyerGang = Gang::create([
            'name' => 'Shadow Bloodz',
            'tag' => 'SHAD',
            'specialty' => 'protection',
            'leader_character_id' => $buyer->id,
            'treasury' => 50000,
            'level' => 2,
        ]);
        $buyer->update(['gang_id' => $buyerGang->id, 'gang_role' => 'leader']);

        $gangProperty = GangProperty::create([
            'gang_id' => $sellerGang->id,
            'property_key' => 'kleine_loods',
            'name' => 'Kleine Opslagloods & Garage',
            'type' => 'storage',
            'purchase_price' => 150000,
            'storage_capacity' => 50,
            'vehicle_slots' => 2,
            'production_status' => 'idle',
        ]);

        // Seller lists property for €100.000
        $response = $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'property',
            'gang_property_id' => $gangProperty->id,
            'amount' => 1,
            'price' => 100000,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('success');

        // Escrow check
        $gangProperty->refresh();
        $this->assertTrue($gangProperty->is_for_sale);

        $listing = MarketplaceListing::where('seller_id', $seller->id)->where('item_type', 'property')->first();
        $this->assertNotNull($listing);

        // Buyer gang leader purchases property
        $buyResponse = $this->actingAs($buyerUser)->post(route('marketplace.buy', $listing->id));
        $buyResponse->assertSessionHas('success');

        $gangProperty->refresh();
        $this->assertEquals($buyerGang->id, $gangProperty->gang_id);

        $sellerGang->refresh();
        $this->assertEquals(110000, $sellerGang->treasury); // 10000 + 100000
    }

    public function test_player_can_cancel_listing_and_reclaim_depot_item(): void
    {
        [$sellerUser, $seller] = $this->createInmate();

        $item = Item::create([
            'name' => 'Kogelwerend Vest',
            'slug' => 'kogelwerend-vest',
            'category' => 'armor',
            'description' => 'Stevig vest',
            'price_kc' => 2000,
            'rarity' => 'rare',
        ]);

        $charItem = CharacterItem::create([
            'character_id' => $seller->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'is_equipped' => false,
        ]);

        $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'depot_item',
            'character_item_id' => $charItem->id,
            'amount' => 1,
            'price' => 3000,
            'currency' => 'cash',
        ]);

        $listing = MarketplaceListing::where('seller_id', $seller->id)->first();
        $this->assertNotNull($listing);

        // Cancel listing
        $cancelResponse = $this->actingAs($sellerUser)->post(route('marketplace.cancel', $listing->id));
        $cancelResponse->assertSessionHas('success');

        // Assert item was restored in depot
        $restoredItem = CharacterItem::where('character_id', $seller->id)->where('item_id', $item->id)->first();
        $this->assertNotNull($restoredItem);
        $this->assertEquals(1, $restoredItem->quantity);
    }

    public function test_player_cannot_list_same_vehicle_twice_or_stack_unique_equipment_via_marketplace(): void
    {
        [$sellerUser, $seller] = $this->createInmate();
        [$buyerUser, $buyer] = $this->createInmate(['cash' => 50000]);

        $vehicle = Vehicle::create([
            'name' => 'Audi RS6 Avant',
            'slug' => 'audi-rs6-avant',
            'category' => 'car',
            'speed_pct' => 160,
            'max_condition' => 100,
            'price_cash' => 45000,
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $seller->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 95,
            'is_active' => true,
        ]);

        // First listing succeeds
        $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'vehicle',
            'character_vehicle_id' => $charVehicle->id,
            'amount' => 1,
            'price' => 20000,
            'currency' => 'cash',
        ])->assertSessionHas('success');

        // Second listing for the same vehicle must fail because it is already for sale
        $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'vehicle',
            'character_vehicle_id' => $charVehicle->id,
            'amount' => 1,
            'price' => 20000,
            'currency' => 'cash',
        ])->assertSessionHas('error');

        $this->assertEquals(1, MarketplaceListing::where('seller_id', $seller->id)->where('item_type', 'vehicle')->count());

        // Unique equipment cannot be bought if buyer already owns 1 copy
        $equipItem = Item::create([
            'name' => 'Boksbeugel Staal',
            'slug' => 'boksbeugel-staal',
            'category' => 'equipment',
            'description' => 'Unieke uitrusting',
            'price_kc' => 5000,
            'rarity' => 'rare',
        ]);

        $sellerEquip = CharacterItem::create([
            'character_id' => $seller->id,
            'item_id' => $equipItem->id,
            'quantity' => 1,
            'is_equipped' => false,
        ]);

        CharacterItem::create([
            'character_id' => $buyer->id,
            'item_id' => $equipItem->id,
            'quantity' => 1,
            'is_equipped' => true,
        ]);

        $this->actingAs($sellerUser)->post(route('marketplace.store'), [
            'item_type' => 'depot_item',
            'character_item_id' => $sellerEquip->id,
            'amount' => 1,
            'price' => 4000,
            'currency' => 'cash',
        ])->assertSessionHas('success');

        $equipListing = MarketplaceListing::where('seller_id', $seller->id)->where('item_type', 'depot_item')->first();
        $this->assertNotNull($equipListing);

        $this->actingAs($buyerUser)
            ->post(route('marketplace.buy', $equipListing->id))
            ->assertSessionHas('error');

        $this->assertFalse((bool) $equipListing->fresh()->is_sold);
        $this->assertEquals(1, CharacterItem::where('character_id', $buyer->id)->where('item_id', $equipItem->id)->value('quantity'));
    }
}
