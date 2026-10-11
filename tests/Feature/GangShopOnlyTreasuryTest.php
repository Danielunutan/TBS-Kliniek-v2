<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangShopOnlyTreasuryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_gang_shop_purchases_strictly_require_money_from_gang_treasury(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'KliniekBende',
            'tag' => 'KB',
            'specialty' => 'Kantine Controle',
            'leader_character_id' => $character->id,
            'treasury' => 0, // Empty gangpot
            'max_members' => 10,
            'protection_points' => 1000,
            'attack_power' => 500,
            'bling_shops' => 0,
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
            'cash' => 200000, // Character has 200.000 cash in wallet
        ]);

        $this->actingAs($user);

        // 1. Trying to buy kiosk directly with personal cash FAILS because gangpot is empty
        $failRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'bling_shops',
            'quantity' => 1,
        ]);

        $failRes->assertSessionHas('error');
        $character->refresh();
        $gang->refresh();
        $this->assertEquals(200000, $character->cash, 'Personal cash must not be deducted directly');
        $this->assertEquals(0, $gang->treasury);
        $this->assertEquals(0, $gang->bling_shops);

        // 2. Donate €100.000 to the gangpot
        $donateRes = $this->post(route('gangs.donate'), [
            'amount' => 100000,
        ]);
        $donateRes->assertSessionHas('success');

        $character->refresh();
        $gang->refresh();
        $this->assertEquals(100000, $character->cash);
        $this->assertEquals(100000, $gang->treasury);

        // 3. Now buy 1 kiosk (€50.000) - succeeds from gangpot!
        $buyKioskRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'bling_shops',
            'quantity' => 1,
        ]);
        $buyKioskRes->assertSessionHas('success');

        $character->refresh();
        $gang->refresh();
        $this->assertEquals(100000, $character->cash, 'Personal cash remains 100.000');
        $this->assertEquals(50000, $gang->treasury, 'Gang treasury deducted to 50.000');
        $this->assertEquals(1, $gang->bling_shops);

        // 4. Buy 2x Protection (2 * €15.000 = €30.000) - succeeds from remaining €50.000 in gangpot
        $buyProtRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'protection',
            'quantity' => 2,
        ]);
        $buyProtRes->assertSessionHas('success');

        $character->refresh();
        $gang->refresh();
        $this->assertEquals(100000, $character->cash);
        $this->assertEquals(20000, $gang->treasury); // 50.000 - 30.000 = 20.000
        $this->assertEquals(11000, $gang->protection_points); // 1000 + 10000

        // 5. Trying to buy 2x Power (costs €30.000, but treasury only has €20.000) FAILS even though character has €100.000 cash
        $failPowerRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'power',
            'quantity' => 2,
        ]);
        $failPowerRes->assertSessionHas('error');

        $character->refresh();
        $gang->refresh();
        $this->assertEquals(100000, $character->cash, 'Personal cash untouched');
        $this->assertEquals(20000, $gang->treasury, 'Treasury untouched');
        $this->assertEquals(500, $gang->attack_power);
    }
}
