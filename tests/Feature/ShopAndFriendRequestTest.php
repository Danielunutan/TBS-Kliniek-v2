<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Friend;
use App\Models\GameMessage;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopAndFriendRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_buy_and_consume_frikandelbroodje(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 500,
            'energy' => 20,
            'max_energy' => 100,
            'health' => 50,
            'max_health' => 100,
        ]);

        $item = Item::where('slug', 'frikandelbroodje')->first();
        if (! $item) {
            $item = Item::create([
                'name' => 'Frikandelbroodje',
                'slug' => 'frikandelbroodje',
                'category' => 'food',
                'price' => 15,
                'power' => 15,
                'protection' => 0,
                'description' => 'Herstelt 15 energie en 10 levenspunten.',
            ]);
        }

        // Reset inventory for test isolation
        CharacterItem::where('character_id', $char->id)->where('item_id', $item->id)->delete();

        $this->actingAs($user);

        // Buy item
        $buyRes = $this->post(route('shop.buy', $item), ['quantity' => 2]);
        $buyRes->assertSessionHas('success');

        $charItem = CharacterItem::where('character_id', $char->id)->where('item_id', $item->id)->first();
        $this->assertNotNull($charItem);
        $this->assertEquals(2, $charItem->quantity);

        // Consume item
        $consumeRes = $this->post(route('shop.consume', $charItem));
        $consumeRes->assertSessionHas('success');

        $char->refresh();
        $charItem->refresh();

        $this->assertEquals(1, $charItem->quantity);
        $this->assertEquals(20 + $item->energy_restore, $char->energy);
        $this->assertEquals(50 + $item->health_restore, $char->health);
    }

    public function test_can_buy_item_using_bank_when_cash_is_insufficient(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 0,
            'bank' => 5000,
        ]);

        $item = Item::where('slug', 'frikandelbroodje')->first();

        $this->actingAs($user);

        // Buy item when cash is 0 but bank has funds
        $buyRes = $this->post(route('shop.buy', $item), ['quantity' => 1]);
        $buyRes->assertSessionHas('success');

        $char->refresh();
        $this->assertEquals(0, $char->cash);
        $this->assertLessThan(5000, $char->bank);
        $this->assertDatabaseHas('character_items', [
            'character_id' => $char->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_friend_request_flow_send_accept_reject(): void
    {
        $userA = User::where('email', 'demo@tbskliniek.nl')->first();
        $charA = $userA->character;

        $userB = User::factory()->create();
        $charB = Character::create([
            'user_id' => $userB->id,
            'name' => 'InmateB',
            'gender' => 'male',
            'level' => 3,
            'cash' => 200,
            'respect' => 5,
        ]);

        $this->actingAs($userA);

        // A sends friend request to B
        $addRes = $this->post(route('friends.add'), [
            'friend_name' => 'InmateB',
        ]);
        $addRes->assertSessionHas('success');

        // Verify pending record created
        $req = Friend::where('character_id', $charA->id)->where('friend_id', $charB->id)->first();
        $this->assertNotNull($req);
        $this->assertEquals('pending', $req->status);

        // Verify notification message created for B
        $msg = GameMessage::where('recipient_id', $charB->id)->where('type', 'friend_request')->first();
        $this->assertNotNull($msg);
        $this->assertEquals($charA->id, $msg->sender_id);

        // B logs in and accepts request
        $this->actingAs($userB);
        $acceptRes = $this->post(route('friends.accept', $req));
        $acceptRes->assertSessionHas('success');

        $req->refresh();
        $this->assertEquals('accepted', $req->status);

        // Reciprocal friendship created for B -> A
        $reciprocal = Friend::where('character_id', $charB->id)->where('friend_id', $charA->id)->first();
        $this->assertNotNull($reciprocal);
        $this->assertEquals('accepted', $reciprocal->status);

        // B removes friendship
        $removeRes = $this->delete(route('friends.remove', $reciprocal));
        $removeRes->assertSessionHas('success');

        $this->assertDatabaseMissing('friends', ['character_id' => $charA->id, 'friend_id' => $charB->id]);
        $this->assertDatabaseMissing('friends', ['character_id' => $charB->id, 'friend_id' => $charA->id]);
    }

    public function test_smuggle_production_with_slug_and_aliases(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'energy' => 100,
            'cash' => 1000,
        ]);

        $this->actingAs($user);

        // Produce cigarettes with Dutch slug
        $produceRes = $this->post(route('smuggle.produce'), [
            'type' => 'sigaretten',
        ]);
        $produceRes->assertSessionHas('success');

        $sigarettenItem = Item::where('slug', 'sigaretten')->first();
        $this->assertNotNull($sigarettenItem);

        $charItem = CharacterItem::where('character_id', $char->id)->where('item_id', $sigarettenItem->id)->first();
        $this->assertNotNull($charItem);
        $this->assertGreaterThanOrEqual(5, $charItem->quantity);
    }

    public function test_profile_renders_with_full_layout_and_friends(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $res = $this->get(route('profile.me'));
        $res->assertOk();
        $res->assertViewHas('character');
        $res->assertViewHas('friends');
        $res->assertSee('Dossier Overzicht');
        $res->assertSee('Vriendenlijst');
        $res->assertSee('Dashboard'); // Sidebar present
    }

    public function test_can_consume_with_fill_max_in_one_click(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'energy' => 40,
            'max_energy' => 100,
            'health' => 60,
            'max_health' => 100,
        ]);

        $item = Item::where('slug', 'frikandelbroodje')->first();
        $charItem = CharacterItem::updateOrCreate(
            ['character_id' => $char->id, 'item_id' => $item->id],
            ['quantity' => 10]
        );

        $this->actingAs($user);

        // Consume with fill_max = 1
        $consumeRes = $this->post(route('shop.consume', $charItem), ['fill_max' => 1]);
        $consumeRes->assertSessionHas('success');

        $char->refresh();
        $charItem->refresh();

        // Energy was deficit 60, per frikandelbroodje is 25 -> ceil(60/25) = 3 needed.
        // Should consume exactly 3 items and reach 100 energy!
        $this->assertEquals(100, $char->energy);
        $this->assertEquals(7, $charItem->quantity);
    }

    public function test_can_buy_item_via_ajax_with_quantity_and_returns_json(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['cash' => 5000]);

        $item = Item::where('slug', 'frikandelbroodje')->first();
        CharacterItem::where('character_id', $char->id)->where('item_id', $item->id)->delete();

        $this->actingAs($user);

        $response = $this->postJson(route('shop.buy', $item), [
            'quantity' => 5,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'quantity_bought' => 5,
            'owned_count' => 5,
        ]);

        $this->assertEquals(5, CharacterItem::where('character_id', $char->id)->where('item_id', $item->id)->value('quantity'));
    }

    public function test_zero_quantity_items_are_not_shown_in_inventory(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        $phoneItem = Item::firstOrCreate(['slug' => 'telefoon'], [
            'name' => 'Gesmokkelde Telefoon',
            'cost' => 1000,
            'is_buyable' => false,
            'category' => 'smokkel',
        ]);

        // Create zero-quantity item
        CharacterItem::updateOrCreate(
            ['character_id' => $char->id, 'item_id' => $phoneItem->id],
            ['quantity' => 0]
        );

        $this->actingAs($user);

        $response = $this->get(route('shop.index'));
        $response->assertOk();

        // The inventory passed to the view should not contain the 0-quantity phone
        /** @var Collection $inventory */
        $inventory = $response->viewData('inventory');
        $this->assertFalse($inventory->contains('item_id', $phoneItem->id));
    }

    public function test_eiwitshake_is_restricted_to_one_per_six_hours_on_purchase(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['cash' => 50000, 'perks' => []]);

        $item = Item::where('slug', 'eiwitshake-bodybuilder')->first();
        if (! $item) {
            $item = Item::create([
                'name' => 'Eiwitshake Bodybuilder',
                'slug' => 'eiwitshake-bodybuilder',
                'category' => 'consumable',
                'price_kc' => 1250,
                'is_buyable' => true,
            ]);
        }

        $this->actingAs($user);

        // First purchase should succeed
        $firstBuy = $this->post(route('shop.buy', $item), ['quantity' => 1]);
        $firstBuy->assertSessionHas('success');

        $char->refresh();
        $this->assertNotNull($char->perks['last_bought_eiwitshake_bodybuilder'] ?? null);

        // Immediate second purchase must be blocked
        $secondBuy = $this->post(route('shop.buy', $item), ['quantity' => 1]);
        $secondBuy->assertSessionHas('error');
        $this->assertStringContainsString('maximaal 1x per 6 uur', session('error'));
    }

    public function test_eiwitshake_is_restricted_to_one_per_six_hours_on_consume(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['strength' => 10, 'perks' => []]);

        $item = Item::where('slug', 'eiwitshake-bodybuilder')->first();
        if (! $item) {
            $item = Item::create([
                'name' => 'Eiwitshake Bodybuilder',
                'slug' => 'eiwitshake-bodybuilder',
                'category' => 'consumable',
                'price_kc' => 1250,
                'is_buyable' => true,
            ]);
        }

        $charItem = CharacterItem::updateOrCreate(
            ['character_id' => $char->id, 'item_id' => $item->id],
            ['quantity' => 5]
        );

        $this->actingAs($user);

        // First consumption should succeed and give +1 strength
        $firstConsume = $this->post(route('shop.consume', $charItem), ['quantity' => 1]);
        $firstConsume->assertSessionHas('success');

        $char->refresh();
        $this->assertEquals(11, $char->strength);
        $this->assertNotNull($char->perks['last_used_eiwitshake_bodybuilder'] ?? null);

        // Immediate second consumption must be blocked by cooldown
        $secondConsume = $this->post(route('shop.consume', $charItem), ['quantity' => 1]);
        $secondConsume->assertSessionHas('error');
        $this->assertStringContainsString('maximaal 1x per 6 uur', session('error'));

        // Strength must remain 11
        $char->refresh();
        $this->assertEquals(11, $char->strength);
    }
}
