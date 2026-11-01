<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket3AuditTest extends TestCase
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
            'name' => 'AuditPatient3',
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 5,
            'respect' => 500,
            'vault_tokens' => 10,
            'masks' => 5,
        ]);
    }

    public function test_number_game_play_handles_atomic_transaction_and_guards(): void
    {
        $this->actingAs($this->user);

        // Cannot play when imprisoned
        $this->character->prison_until = now()->addMinutes(10);
        $this->character->is_in_prison = true;
        $this->character->save();

        $res = $this->post(route('numbergame.play'), [
            'number' => 10,
            'bet' => 100,
        ]);
        $res->assertSessionHas('error', 'Vanuit de isoleercel kun je niet gokken!');

        // Clear prison
        $this->character->prison_until = null;
        $this->character->is_in_prison = false;
        $this->character->save();

        // Insufficient cash
        $this->character->cash = 10;
        $this->character->save();

        $res = $this->post(route('numbergame.play'), [
            'number' => 10,
            'bet' => 100,
        ]);
        $res->assertSessionHas('error');

        // Play succeeds and sets cooldown
        $this->character->cash = 5000;
        $this->character->save();

        $res = $this->post(route('numbergame.play'), [
            'number' => 10,
            'bet' => 100,
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'casino_numbergame'));
    }

    public function test_roulette_play_handles_atomic_transaction_and_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('roulette.play'), [
            'bet_type' => 'rood',
            'bet' => 100,
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'casino_roulette'));

        // Consecutive attempt is blocked by cooldown
        $res2 = $this->post(route('roulette.play'), [
            'bet_type' => 'rood',
            'bet' => 100,
        ]);
        $res2->assertSessionHas('error');
    }

    public function test_rps_play_handles_atomic_transaction_and_cooldown(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('rps.play'), [
            'choice' => 'steen',
            'bet' => 100,
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'casino_rps'));
    }

    public function test_slots_spin_json_and_transaction_protection(): void
    {
        $this->actingAs($this->user);

        $res = $this->postJson(route('slots.spin'), [
            'bet' => 100,
        ]);
        $res->assertStatus(200);
        $res->assertJsonStructure(['success', 'reels', 'type', 'message']);
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'casino_slots'));
    }

    public function test_package_intercept_runs_inside_transaction_with_lock(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('packages.intercept'), [
            'package_id' => 1,
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'package'));
    }

    public function test_pickpocket_steal_runs_inside_transaction_with_lock(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('pickpocket.steal'), [
            'action_id' => 1,
            'technique' => 'schaduw',
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'pickpocket'));
    }

    public function test_smuggle_produce_runs_inside_transaction_and_locks(): void
    {
        $this->actingAs($this->user);

        $res = $this->post(route('smuggle.produce'), [
            'type' => 'sigaretten',
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'produce_sigaretten'));
        $this->assertEquals(95, $this->character->fresh()->energy);
    }

    public function test_vault_buy_actions_run_inside_transaction_with_lock(): void
    {
        $this->actingAs($this->user);

        $this->character->vault_tokens = 5;
        $this->character->save();

        // Buy code costs 4 tokens
        $res = $this->post(route('vault.buy-code'));
        $res->assertRedirect();
        $this->assertEquals(1, $this->character->fresh()->vault_tokens);

        // Buy hint costs 1 token
        $res2 = $this->post(route('vault.buy-hint'));
        $res2->assertRedirect();
        $this->assertEquals(0, $this->character->fresh()->vault_tokens);

        // Cannot buy jammer (costs 2 tokens)
        $res3 = $this->post(route('vault.buy-jammer'));
        $res3->assertSessionHas('error');
    }

    public function test_treatment_consultation_negative_outcome_consumes_energy_safely(): void
    {
        $this->actingAs($this->user);

        $this->character->last_energy_refill_at = now();
        $this->character->energy = 50;
        $this->character->treatment_score = 30;
        $this->character->save();

        // Submit consultation with negative aggressive answers
        $res = $this->post(route('treatment.submit'), [
            'answers' => [
                1 => 'c',
                2 => 'c',
                3 => 'c',
            ],
        ]);

        $res->assertRedirect();
        $fresh = $this->character->fresh();
        // 50 energy minus 30 = 20
        $this->assertEquals(20, $fresh->energy);
        $this->assertNotNull($fresh->sedated_until);
    }

    public function test_marketplace_buy_validates_vehicle_and_property_escrow(): void
    {
        $sellerUser = User::factory()->create(['is_onboarded' => true]);
        $seller = Character::factory()->create([
            'user_id' => $sellerUser->id,
            'name' => 'SellerPatient',
            'cash' => 1000,
        ]);

        $vehicle = Vehicle::firstOrCreate(['slug' => 'test-wagen'], [
            'name' => 'Test Wagen',
            'price' => 5000,
            'speed' => 50,
            'acceleration' => 50,
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $seller->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 100,
            'is_active' => false,
            'is_for_sale' => false, // Not for sale!
        ]);

        $listing = MarketplaceListing::create([
            'seller_id' => $seller->id,
            'item_type' => 'vehicle',
            'related_id' => $charVehicle->id,
            'title' => 'Test Wagen Escrow',
            'description' => 'Escrow check',
            'amount' => 1,
            'price' => 500,
            'currency' => 'cash',
            'is_sold' => false,
            'expires_at' => now()->addDays(2),
        ]);

        $this->actingAs($this->user);
        $this->character->cash = 5000;
        $this->character->save();

        // Attempting to buy when vehicle is_for_sale is false should fail
        $res = $this->post(route('marketplace.buy', $listing->id));
        $res->assertSessionHas('error');

        // Now set is_for_sale to true
        $charVehicle->is_for_sale = true;
        $charVehicle->save();

        $resSuccess = $this->post(route('marketplace.buy', $listing->id));
        $resSuccess->assertRedirect();
        $this->assertTrue((bool) $listing->fresh()->is_sold);
        $this->assertEquals($this->character->id, $charVehicle->fresh()->character_id);
    }

    public function test_comatose_guard_blocks_actions_across_casino_and_packages(): void
    {
        $this->actingAs($this->user);

        $this->character->coma_until = now()->addHour();
        $this->character->save();

        $this->assertTrue($this->character->isComatose());

        // Number game
        $this->post(route('numbergame.play'), ['number' => 5, 'bet' => 100])
            ->assertSessionHas('error');

        // Roulette
        $this->post(route('roulette.play'), ['bet_type' => 'rood', 'bet' => 100])
            ->assertSessionHas('error');

        // RPS
        $this->post(route('rps.play'), ['choice' => 'steen', 'bet' => 100])
            ->assertSessionHas('error');

        // Slots
        $this->postJson(route('slots.spin'), ['bet' => 100])
            ->assertStatus(422);

        // Packages
        $this->post(route('packages.intercept'), ['package_id' => 1])
            ->assertSessionHas('error');

        // Smuggle
        $this->post(route('smuggle.produce'), ['type' => 'sigaretten'])
            ->assertSessionHas('error');

        // Vault
        $this->post(route('vault.buy-code'))
            ->assertSessionHas('error');
    }

    public function test_marketplace_buy_validates_property_escrow(): void
    {
        $sellerUser = User::factory()->create(['is_onboarded' => true]);
        $sellerGang = Gang::factory()->create([
            'name' => 'SellerGang',
            'tag' => 'SELL',
            'treasury' => 1000,
        ]);
        $seller = Character::factory()->create([
            'user_id' => $sellerUser->id,
            'name' => 'SellerOfficer',
            'gang_id' => $sellerGang->id,
            'gang_role' => 'leader',
        ]);

        $buyerGang = Gang::factory()->create([
            'name' => 'BuyerGang',
            'tag' => 'BUYG',
            'treasury' => 50000,
        ]);
        $this->character->gang_id = $buyerGang->id;
        $this->character->gang_role = 'leader';
        $this->character->cash = 50000;
        $this->character->save();

        $property = GangProperty::create([
            'gang_id' => $sellerGang->id,
            'property_key' => 'laboratorium',
            'name' => 'Geheim Lab',
            'type' => 'drugs',
            'purchase_price' => 10000,
            'is_for_sale' => false, // Not for sale
            'production_status' => 'idle',
        ]);

        $listing = MarketplaceListing::create([
            'seller_id' => $seller->id,
            'item_type' => 'property',
            'related_id' => $property->id,
            'title' => 'Geheim Lab Listing',
            'description' => 'Property escrow',
            'amount' => 1,
            'price' => 10000,
            'currency' => 'cash',
            'metadata' => [
                'property_id' => $property->id,
                'property_key' => 'laboratorium',
                'seller_gang_id' => $sellerGang->id,
            ],
            'is_sold' => false,
            'expires_at' => now()->addDays(2),
        ]);

        $this->actingAs($this->user);

        // Fails because property is not marked for sale
        $res = $this->post(route('marketplace.buy', $listing->id));
        $res->assertSessionHas('error');

        // Mark property for sale
        $property->is_for_sale = true;
        $property->save();

        $resSuccess = $this->post(route('marketplace.buy', $listing->id));
        $resSuccess->assertRedirect();
        $this->assertTrue((bool) $listing->fresh()->is_sold);
        $this->assertEquals($buyerGang->id, $property->fresh()->gang_id);
    }
}
