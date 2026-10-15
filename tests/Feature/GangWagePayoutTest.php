<?php

namespace Tests\Feature;

use App\Models\Gang;
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

        // Base 150 + 3 * 50 = 300 hourly
        $this->assertEquals(300, $service->calculateHourlyWage($gang));
        $this->assertEquals(7200, $service->calculateDailyWage($gang));
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
            'bling_shops' => 2, // 150 + 100 = 250 / hour
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'leader';
        $character->cash = 1000;
        $character->bank = 5000;
        // Last paid 3 hours ago
        $character->last_gang_wage_at = Carbon::now()->subHours(3);
        $character->save();

        $this->actingAs($user);

        $response = $this->get(route('gangs.index'));
        $response->assertStatus(200);

        $character->refresh();

        // 3 hours * 250 = 750 added to cash (bank untouched)
        $this->assertEquals(1750, $character->cash);
        $this->assertEquals(5000, $character->bank);
        $response->assertSee('Bende Uurloon Ontvangen');
        $response->assertSee('UURLOON €250');
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
            'bling_shops' => 1, // 200 / hour
            'leader_character_id' => $character->id,
        ]);

        $character->gang_id = $gang->id;
        $character->cash = 2000;
        $character->bank = 3000;
        $character->last_gang_wage_at = Carbon::now()->subMinutes(130); // 2 hours
        $character->save();

        $this->actingAs($user);

        $response = $this->post(route('gangs.claim-wage'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(2400, $character->cash); // 2000 + 2 * 200
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
}
