<?php

namespace Tests\Feature;

use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\User;
use App\Services\GangWageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GangWagePayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_wage_service_calculates_wages_accurately(): void
    {
        $service = app(GangWageService::class);

        $gang = Gang::create([
            'name' => 'Test Syndicate',
            'tag' => 'TS',
            'specialty' => 'Kantine Controle',
            'treasury' => 5000,
            'bling_shops' => 3,
        ]);

        // Base 150 + 3 * 100 = 450 hourly
        $this->assertEquals(450, $service->calculateHourlyWage($gang));
        $this->assertEquals(10800, $service->calculateDailyWage($gang));
    }

    public function test_visiting_gangs_page_automatically_pays_out_pending_wages(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'TBS Bosses',
            'tag' => 'TBSB',
            'specialty' => 'Kantine Controle',
            'treasury' => 10000,
            'bling_shops' => 2, // 150 + 200 = 350 / hour
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'member';
        $character->level = 1;
        $character->cash = 1000;
        $character->bank = 5000;
        // Last paid 3 hours ago
        $character->last_gang_wage_at = Carbon::now()->subHours(3);
        $character->save();

        $this->actingAs($user);

        $response = $this->get(route('gangs.index'));
        $response->assertStatus(200);

        $character->refresh();

        // 3 hours * 350 = 1050 added to cash (bank untouched)
        $this->assertEquals(2050, $character->cash);
        $this->assertEquals(5000, $character->bank);
        $response->assertSee('Bende Uurloon Ontvangen');
        $response->assertSee('UURLOON €350');
    }

    public function test_gang_pay_wages_artisan_command(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Night Owls',
            'tag' => 'NOWL',
            'specialty' => 'Kantine Controle',
            'treasury' => 1000,
            'bling_shops' => 0, // 150 / hour
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'member';
        $character->level = 1;
        $character->cash = 500;
        $character->bank = 10000;
        $character->last_gang_wage_at = Carbon::now()->subHours(2);
        $character->save();

        $this->artisan('gang:pay-wages')
            ->expectsOutputToContain('Uitbetaling voltooid!')
            ->assertSuccessful();

        $character->refresh();
        // 2 hours * 150 = 300 added to cash (bank untouched)
        $this->assertEquals(800, $character->cash);
        $this->assertEquals(10000, $character->bank);
    }

    public function test_manual_claim_wage_endpoint(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Clean Money Crew',
            'tag' => 'CMC',
            'specialty' => 'Kantine Controle',
            'treasury' => 1000,
            'bling_shops' => 1, // 150 + 100 = 250 / hour
            'leader_character_id' => $character->id,
        ]);

        // Freeze the clock mid-hour: wages count whole clock hours, so the result must not depend on the minute the suite runs.
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 12, 30, 0));

        $character->gang_id = $gang->id;
        $character->gang_role = 'member';
        $character->level = 1;
        $character->cash = 2000;
        $character->bank = 3000;
        $character->last_gang_wage_at = Carbon::now()->subMinutes(130); // 2 hours
        $character->save();

        $this->actingAs($user);

        $response = $this->post(route('gangs.claim-wage'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(2500, $character->cash); // 2000 + 2 * 250
        $this->assertEquals(3000, $character->bank); // Bank remains untouched
    }

    public function test_officer_can_upload_gang_avatar_and_banner(): void
    {
        Storage::fake('public');

        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Visual Squad',
            'tag' => 'VS',
            'specialty' => 'Kantine Controle',
            'treasury' => 5000,
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'leader';
        $character->save();

        $this->actingAs($user);

        // Upload avatar
        $fakeAvatar = UploadedFile::fake()->image('emblem.png', 200, 200);
        $resAvatar = $this->post(route('gangs.settings'), [
            'action_type' => 'avatar',
            'avatar' => $fakeAvatar,
        ]);
        $resAvatar->assertSessionHas('success');

        $gang->refresh();
        $this->assertNotNull($gang->avatar);
        $this->assertStringContainsString('gangs/avatars', $gang->avatar);

        // Upload banner
        $fakeBanner = UploadedFile::fake()->image('banner.jpg', 800, 300);
        $resBanner = $this->post(route('gangs.settings'), [
            'action_type' => 'banner',
            'banner' => $fakeBanner,
        ]);
        $resBanner->assertSessionHas('success');

        $gang->refresh();
        $this->assertNotNull($gang->banner);
        $this->assertStringContainsString('gangs/banners', $gang->banner);

        // Verify page loads with banner and avatar rendered
        $resPage = $this->get(route('gangs.index'));
        $resPage->assertStatus(200);
        $resPage->assertSee($gang->avatar_url);
        $resPage->assertSee($gang->banner_url);
        $resPage->assertSee('Visual Squad');
    }

    public function test_gang_properties_and_luxury_bonuses_increase_hourly_wage(): void
    {
        $service = app(GangWageService::class);

        $gang = Gang::create([
            'name' => 'Property Moguls',
            'tag' => 'PM',
            'specialty' => 'Vastgoed & Witwassen',
            'treasury' => 500000,
            'bling_shops' => 5,
        ]);

        // Base with 5 shops: 150 + 5 * 100 = 650
        $this->assertEquals(650, $service->calculateHourlyWage($gang));

        // Add a warehouse (+€1.000) and a luxury penthouse (+€20.000 base, +12% wage bonus)
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'kleine_loods',
            'name' => 'Kleine Loods',
            'type' => 'storage',
            'purchase_price' => 150000,
        ]);

        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'luxe_penthouse',
            'name' => 'Luxe Penthouse',
            'type' => 'luxury',
            'purchase_price' => 2750000,
        ]);

        $gang->load('properties');

        // Subtotal = 650 (base) + 1000 (loods) + 20000 (penthouse) = 21650
        // Multiplier = 1 + 12% = 1.12
        // Expected = round(21650 * 1.12) = 24248
        $this->assertEquals(24248, $service->calculateHourlyWage($gang));
    }

    public function test_hourly_wage_is_globally_synchronized_and_creates_notifications(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 14:15:30'));

        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Sync Crew',
            'tag' => 'SYNC',
            'specialty' => 'Kantine Controle',
            'treasury' => 10000,
            'bling_shops' => 1,
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'member';
        $character->level = 1;
        $character->cash = 1000;
        $character->last_gang_wage_at = Carbon::parse('2026-10-28 13:00:00');
        $character->save();

        $service = app(GangWageService::class);
        $details = $service->getWageDetails($character);

        // 14:15:30 -> next payout is at 15:00:00 (44m 30s = 2670 seconds)
        $this->assertEquals(2670, $details['seconds_until_next']);
        $this->assertEquals('44m 30s', $details['next_payout_human']);
        $this->assertEquals(1, $details['pending_hours']);

        // Fast forward to top of the hour (15:00:00)
        Carbon::setTestNow(Carbon::parse('2026-10-28 15:00:00'));

        $this->artisan('gang:pay-wages')->assertSuccessful();

        $character->refresh();
        // 13:00 to 15:00 = 2 hours * 250 = 500
        $this->assertEquals(1500, $character->cash);
        $this->assertEquals('2026-10-28 15:00:00', $character->last_gang_wage_at->format('Y-m-d H:i:s'));

        // Notification created
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $character->id,
            'type' => 'gang_wage',
        ]);

        Carbon::setTestNow();
    }
}
