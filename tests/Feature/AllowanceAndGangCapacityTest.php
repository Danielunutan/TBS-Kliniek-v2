<?php

namespace Tests\Feature;

use App\Models\Gang;
use App\Models\User;
use App\Services\AllowanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllowanceAndGangCapacityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_gang_member_capacity_upgrade_costs_25_stars_per_slot(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Capacity Squad',
            'tag' => 'CSQ',
            'specialty' => 'Kantine Controle',
            'max_members' => 10,
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'leader';
        $character->stars = 100;
        $character->save();

        $this->actingAs($user);

        // Buy 2 extra slots = 2 * 25 = 50 stars
        $response = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'max_members',
            'quantity' => 2,
        ]);

        $response->assertSessionHas('success');

        $character->refresh();
        $gang->refresh();

        $this->assertEquals(50, $character->stars); // 100 - 50 = 50
        $this->assertEquals(12, $gang->max_members); // 10 + 2 = 12
    }

    public function test_non_gang_member_receives_1000_euro_allowance_per_15_minutes(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $character->gang_id = null;
        $character->bank = 500;
        // Last paid 45 minutes ago = 3 intervals of 15 min = €3.000
        $character->last_allowance_at = Carbon::now()->subMinutes(45);
        $character->save();

        $service = app(AllowanceService::class);
        $result = $service->payoutPendingAllowance($character);

        $this->assertNotNull($result);
        $this->assertEquals(3, $result['cycles']);
        $this->assertEquals(3000, $result['amount']);

        $character->refresh();
        $this->assertEquals(3500, $character->bank); // 500 + 3000
    }

    public function test_gang_member_does_not_receive_non_gang_allowance(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Elite Syndicate',
            'tag' => 'ELS',
            'specialty' => 'Kantine Controle',
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->bank = 1000;
        $character->last_allowance_at = Carbon::now()->subMinutes(60);
        $character->save();

        $service = app(AllowanceService::class);
        $result = $service->payoutPendingAllowance($character);

        $this->assertNull($result);

        $character->refresh();
        $this->assertEquals(1000, $character->bank);
    }

    public function test_visiting_dashboard_and_bank_automatically_refreshes_allowance(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $character->gang_id = null;
        $character->bank = 2000;
        $character->last_allowance_at = Carbon::now()->subMinutes(30); // 2 intervals = €2.000
        $character->save();

        $this->actingAs($user);

        // Visiting dashboard triggers energyService->refresh which pays allowance
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('TBS Patiënten Uitkering');
        $response->assertSee('€1.000');

        $character->refresh();
        $this->assertEquals(4000, $character->bank); // 2000 + 2000

        // Visiting bank also shows uitkering overview
        $bankResponse = $this->get(route('bank.index'));
        $bankResponse->assertStatus(200);
        $bankResponse->assertSee('TBS Patiënten Uitkering');
        $bankResponse->assertSee('Actief');
    }

    public function test_patients_pay_allowances_artisan_command(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $character->gang_id = null;
        $character->bank = 100;
        $character->last_allowance_at = Carbon::now()->subMinutes(15);
        $character->save();

        $this->artisan('patients:pay-allowances')
            ->expectsOutputToContain('Uitbetaling voltooid!')
            ->assertSuccessful();

        $character->refresh();
        $this->assertEquals(1100, $character->bank); // 100 + 1000
    }
}
