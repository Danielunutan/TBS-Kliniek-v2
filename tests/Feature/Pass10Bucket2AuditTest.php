<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\Friend;
use App\Models\Item;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket2AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_onboarded' => true]);
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'AuditPatient2',
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 80,
            'max_health' => 100,
            'level' => 5,
            'respect' => 500,
            'vault_tokens' => 10,
            'masks' => 5,
        ]);
    }

    public function test_daycare_perform_executes_atomically_and_enforces_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('daycare.perform'), ['job_id' => 1]);
        $res->assertSessionHas('success');

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'dagbesteding'));

        // Concurrent attempt while on cooldown should be rejected
        $res2 = $this->post(route('daycare.perform'), ['job_id' => 1]);
        $res2->assertSessionHas('error');
    }

    public function test_depot_sell_item_and_masks_use_economy_service(): void
    {
        $this->actingAs($this->user);

        $item = Item::create([
            'name' => 'Test Item',
            'slug' => 'test-item',
            'description' => 'Test',
            'category' => 'contraband',
            'rarity' => 'common',
            'price_kc' => 1000,
            'is_buyable' => true,
        ]);

        $charItem = CharacterItem::create([
            'character_id' => $this->character->id,
            'item_id' => $item->id,
            'quantity' => 2,
        ]);

        $initialCash = $this->character->cash;
        $res = $this->post(route('depot.sell-item', $charItem), ['quantity' => 1]);
        $res->assertSessionHas('success');

        $this->character->refresh();
        $this->assertGreaterThan($initialCash, $this->character->cash);

        // Sell masks
        $masksBefore = $this->character->masks;
        $cashBefore = $this->character->cash;
        $resMasks = $this->post(route('depot.sell-masks'), ['quantity' => 2]);
        $resMasks->assertSessionHas('success');

        $this->character->refresh();
        $this->assertEquals($masksBefore - 2, $this->character->masks);
        $this->assertGreaterThan($cashBefore, $this->character->cash);
    }

    public function test_discord_shield_link_prevents_duplicate_discord_id(): void
    {
        $otherUser = User::factory()->create([
            'discord_id' => '123456789012345678',
        ]);

        $this->actingAs($this->user);
        session(['verified_discord_id' => '123456789012345678']);

        $res = $this->post(route('discord.link'));
        $res->assertSessionHas('error', 'Dit Discord account is al gekoppeld aan een ander spelersaccount.');

        $this->user->refresh();
        $this->assertNull($this->user->discord_id);
    }

    public function test_dropping_catch_tennisball_and_order_drone_enforce_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('droppings.tennisball'));
        $this->assertTrue($res->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'dropping_tennisball'));

        // Immediate retry blocked by cooldown
        $resRetry = $this->post(route('droppings.tennisball'));
        $resRetry->assertSessionHas('error');

        // Order drone (reset prison & energy if caught during tennisball)
        $this->character->update(['prison_until' => null, 'energy' => 100, 'cash' => 50000]);

        $resDrone = $this->post(route('droppings.drone'));
        $this->assertTrue($resDrone->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'dropping_drone'));

        $resDroneRetry = $this->post(route('droppings.drone'));
        $resDroneRetry->assertSessionHas('error');
    }

    public function test_extort_executes_under_transaction_and_enforces_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('extort.extort'), ['target_id' => 1]);
        $this->assertTrue($res->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'extort'));

        $resRetry = $this->post(route('extort.extort'), ['target_id' => 1]);
        $resRetry->assertSessionHas('error');
    }

    public function test_highlow_play_executes_atomically_under_transaction(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('highlow.play'), [
            'choice' => 'hoger',
            'bet' => 100,
        ]);
        $this->assertTrue($res->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'casino_highlow'));

        // Anti-spam cooldown rejects rapid request
        $resRetry = $this->post(route('highlow.play'), [
            'choice' => 'hoger',
            'bet' => 100,
        ]);
        $resRetry->assertSessionHas('error');
    }

    public function test_hustle_attempt_executes_under_transaction_and_enforces_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('hustle.attempt'), ['hustle_id' => 1]);
        $this->assertTrue($res->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'hustle'));

        $resRetry = $this->post(route('hustle.attempt'), ['hustle_id' => 1]);
        $resRetry->assertSessionHas('error');
    }

    public function test_infirmary_take_and_palm_execute_atomically(): void
    {
        $this->actingAs($this->user);

        // Character has 80 HP, take heals 45 (caps at 100)
        $res = $this->post(route('infirmary.take'));
        $res->assertSessionHas('success');

        $this->character->refresh();
        $this->assertEquals(100, $this->character->health);
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'infirmary_take'));

        // Retry take blocked by cooldown
        $resRetry = $this->post(route('infirmary.take'));
        $resRetry->assertSessionHas('error');

        // Palm pill
        $item = Item::firstOrCreate(['slug' => 'strip-oxazepam'], [
            'name' => 'Strip Oxazepam',
            'description' => 'Test',
            'category' => 'contraband',
            'rarity' => 'common',
            'price_kc' => 500,
            'is_buyable' => true,
        ]);

        $resPalm = $this->post(route('infirmary.palm'));
        $this->assertTrue($resPalm->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'infirmary_palm'));

        $resPalmRetry = $this->post(route('infirmary.palm'));
        $resPalmRetry->assertSessionHas('error');
    }

    public function test_locker_crack_executes_under_transaction_and_enforces_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('lockers.crack'), ['locker_id' => 1]);
        $this->assertTrue($res->isRedirect());

        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'locker'));

        $resRetry = $this->post(route('lockers.crack'), ['locker_id' => 1]);
        $resRetry->assertSessionHas('error');
    }

    public function test_garage_buy_and_repair_records_bank_transaction_when_using_bank(): void
    {
        $this->actingAs($this->user);

        $this->character->update([
            'cash' => 10,
            'bank' => 50000,
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Snelle Bolide',
            'slug' => 'snelle-bolide',
            'type' => 'sedan',
            'brand' => 'TBS Motors',
            'icon' => 'fa-car',
            'badge_color' => 'amber',
            'speed_pct' => 80,
            'reliability_pct' => 90,
            'min_level' => 1,
            'description' => 'Test car',
            'price_cash' => 2000,
            'price_stars' => 0,
            'is_available' => true,
        ]);

        $res = $this->post(route('garage.buy', $vehicle), ['payment' => 'cash']);
        $res->assertSessionHas('success');

        $this->character->refresh();
        $this->assertEquals(0, $this->character->cash);
        $this->assertEquals(48010, $this->character->bank);

        $this->assertDatabaseHas('bank_transactions', [
            'character_id' => $this->character->id,
            'type' => 'withdrawal',
            'source' => 'bank',
            'amount' => 1990,
        ]);

        // Now repair vehicle with bank
        $charVehicle = CharacterVehicle::where('character_id', $this->character->id)->first();
        $charVehicle->update(['current_condition' => 50]);

        $resRepair = $this->post(route('garage.repair', $charVehicle));
        $resRepair->assertSessionHas('success');

        $this->assertDatabaseHas('bank_transactions', [
            'character_id' => $this->character->id,
            'type' => 'withdrawal',
            'source' => 'bank',
            'description' => "Voertuig Revisie ({$vehicle->name})",
        ]);
    }

    public function test_friend_cooldown_using_safe_future_comparison(): void
    {
        $this->actingAs($this->user);

        $friendUser = User::factory()->create();
        $friendCharacter = Character::factory()->create([
            'user_id' => $friendUser->id,
            'name' => 'BesteVriend',
        ]);

        $friendship = Friend::create([
            'character_id' => $this->character->id,
            'friend_id' => $friendCharacter->id,
            'status' => 'accepted',
            'last_respected_at' => now()->subHours(2),
        ]);

        session(['friend_captcha_ans' => 7]);

        $res = $this->post(route('friends.respect', $friendship), [
            'captcha_answer' => 7,
        ]);
        $res->assertSessionHas('error');
        $this->assertStringContainsString('vandaag al respect gegeven', session('error'));
    }
}
