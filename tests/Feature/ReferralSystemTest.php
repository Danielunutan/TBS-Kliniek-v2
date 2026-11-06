<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dashboard_displays_personal_referral_link_and_stats(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Nodig Vrienden Uit');
        $response->assertSee('?ref='.$char->username);
        $response->assertSee('3 Spelers = 1 Maand VIP');
    }

    public function test_neat_referral_link_generated_for_unicode_or_stylized_character_names(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => '𝕯𝖆𝖓𝖎𝖊𝖑', // pure stylized unicode with no ascii
            'cash' => 100,
            'bank' => 0,
            'level' => 1,
            'xp' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'respect' => 10,
        ]);

        $referralService = app(ReferralService::class);
        $stats = $referralService->getStats($char);

        // Neat link falls back to character ID when no ascii alphanumeric characters exist
        $this->assertEquals(rtrim(url('/'), '/').'/?ref='.$char->id, $stats['link']);

        // Registration using the numeric ID correctly credits the referrer
        $friendUser = User::factory()->create();
        $friend = Character::create([
            'user_id' => $friendUser->id,
            'name' => 'FriendUser',
            'cash' => 100,
            'bank' => 0,
            'level' => 1,
            'xp' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'respect' => 10,
        ]);

        $referral = $referralService->recordReferral($friend, (string) $char->id);
        $this->assertNotNull($referral);
        $this->assertEquals($char->id, $referral->referrer_id);
    }

    public function test_registration_with_referral_grants_vip_after_three_signups(): void
    {
        $referrerUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $referrerChar = $referrerUser->character;
        $referrerChar->update([
            'is_vip' => false,
            'vip_expires_at' => null,
        ]);

        $referralService = app(ReferralService::class);

        $turnstile = $this->createStub(TurnstileService::class);
        $turnstile->method('verify')->willReturn(true);
        $this->app->instance(TurnstileService::class, $turnstile);

        // 1. First friend registers
        $res1 = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.1'])->post(route('register.post'), [
            'username' => 'VriendEen',
            'email' => 'vriend1@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'gender' => 'male',
            'terms' => '1',
            'ref' => $referrerChar->name,
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);
        $res1->assertRedirect();

        $char1 = Character::where('name', 'VriendEen')->first();
        $this->assertNotNull($char1);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrerChar->id,
            'referred_id' => $char1->id,
            'rewarded' => false,
        ]);

        $referrerChar->refresh();
        $this->assertFalse($referrerChar->isVip());

        Auth::logout();

        // 2. Second friend registers
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.2'])->post(route('register.post'), [
            'username' => 'VriendTwee',
            'email' => 'vriend2@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'gender' => 'male',
            'terms' => '1',
            'ref' => $referrerChar->name,
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $referrerChar->refresh();
        $this->assertFalse($referrerChar->isVip());

        Auth::logout();

        // 3. Third friend registers -> triggers 1 month VIP!
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.3'])->post(route('register.post'), [
            'username' => 'VriendDrie',
            'email' => 'vriend3@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'gender' => 'female',
            'terms' => '1',
            'ref' => $referrerChar->name,
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $referrerChar->refresh();
        $this->assertTrue($referrerChar->isVip());
        $this->assertNotNull($referrerChar->vip_expires_at);
        $this->assertTrue($referrerChar->vip_expires_at->isFuture());

        // Verify all 3 referrals are marked as rewarded
        $rewardedCount = Referral::where('referrer_id', $referrerChar->id)->where('rewarded', true)->count();
        $this->assertEquals(3, $rewardedCount);

        // Verify celebration message was delivered
        $message = GameMessage::where('recipient_id', $referrerChar->id)
            ->where('type', 'vip_reward')
            ->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Gratis VIP', $message->subject);
    }

    public function test_admin_referrals_log_page_renders_with_data(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        if (! $adminUser) {
            $adminUser = User::factory()->create(['role' => 'admin']);
        }

        $response = $this->actingAs($adminUser)->get(route('admin.referrals'));
        $response->assertOk();
        $response->assertSee('Uitbetalingen Logboek');
        $response->assertSee('Totaal Aangebracht');
    }
}
