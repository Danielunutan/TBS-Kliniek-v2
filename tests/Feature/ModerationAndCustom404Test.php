<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Models\UserWarning;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationAndCustom404Test extends TestCase
{
    use RefreshDatabase;

    private function createStaffUser(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'Hoofdbewaker_'.rand(100, 999),
            'level' => 10,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 500,
            'last_active_at' => now(),
        ]);

        return $user;
    }

    private function createNormalUser(): User
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_staff' => false,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'KliniekBoevenLid_'.rand(100, 999),
            'level' => 1,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 1000,
            'bank' => 2000,
            'stars' => 5,
            'respect' => 50,
            'last_active_at' => now(),
        ]);

        return $user;
    }

    public function test_custom_404_page_renders_in_tbs_style(): void
    {
        $response = $this->get('/niet-bestaande-gang-in-kliniek-xyz-987');

        $response->assertStatus(404);
        $response->assertSee('Verdwaald in de Beveiligde Vleugel');
        $response->assertSee('Code 404');
        $response->assertSee('Hé boef! Deze cel, gang of pagina bestaat niet');
        $response->assertSee('Kliniek Snelmenu');
        $response->assertSee('Kennisbank');
        $response->assertSee('Support');
    }

    public function test_staff_can_issue_warning_and_player_can_acknowledge(): void
    {
        $staff = $this->createStaffUser();
        $player = $this->createNormalUser();

        // 1. Staff gives warning
        $response = $this->actingAs($staff)->post(route('admin.moderation.warn', $player), [
            'reason' => 'Ongepast taalgebruik op het prikbord.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_warnings', [
            'user_id' => $player->id,
            'issuer_id' => $staff->id,
            'reason' => 'Ongepast taalgebruik op het prikbord.',
            'acknowledged_at' => null,
        ]);

        // 2. Player sees warning banner on dashboard
        $dashResponse = $this->actingAs($player)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Officiële Waarschuwing');
        $dashResponse->assertSee('Ongepast taalgebruik op het prikbord.');

        // 3. Player acknowledges warning
        $warning = UserWarning::where('user_id', $player->id)->firstOrFail();
        $ackResponse = $this->actingAs($player)->post(route('warning.acknowledge', $warning));
        $ackResponse->assertRedirect();

        $this->assertTrue($warning->fresh()->isAcknowledged());
    }

    public function test_staff_can_ban_user_and_banned_user_sees_custom_ban_screen(): void
    {
        $staff = $this->createStaffUser();
        $player = $this->createNormalUser();

        // 1. Staff bans player for 1 day
        $response = $this->actingAs($staff)->post(route('admin.moderation.ban', $player), [
            'reason' => 'Verdacht van multi-accounting en exploit misbruik.',
            'duration' => '1_day',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $player->refresh();
        $this->assertTrue($player->is_banned);
        $this->assertNotNull($player->banned_until);
        $this->assertEquals('Verdacht van multi-accounting en exploit misbruik.', $player->ban_reason);

        // 2. Player accesses site, gets blocked by EnsureUserIsNotBanned middleware with 403
        $blockedResponse = $this->actingAs($player)->get(route('dashboard'));
        $blockedResponse->assertStatus(403);
        $blockedResponse->assertSee('Toegang Ontzegd tot de Kliniek');
        $blockedResponse->assertSee('Verdacht van multi-accounting en exploit misbruik.');
        $blockedResponse->assertSee('Uitloggen');

        // 3. Staff unbans player
        $unbanResponse = $this->actingAs($staff)->post(route('admin.moderation.unban', $player));
        $unbanResponse->assertRedirect();

        $player->refresh();
        $this->assertFalse($player->is_banned);
        $this->assertNull($player->banned_until);

        // 4. Player can access site again
        $allowedResponse = $this->actingAs($player)->get(route('dashboard'));
        $allowedResponse->assertStatus(200);
    }

    public function test_expired_temporary_ban_automatically_unbans_player(): void
    {
        $staff = $this->createStaffUser();
        $player = $this->createNormalUser();

        // Ban until 5 minutes ago
        $player->ban('Tijdelijke cooldown ban', Carbon::now()->subMinutes(5), $staff);
        $this->assertTrue($player->is_banned);

        // Access dashboard -> auto unbans and loads page
        $response = $this->actingAs($player)->get(route('dashboard'));
        $response->assertStatus(200);

        $player->refresh();
        $this->assertFalse($player->is_banned);
    }

    public function test_banned_player_can_still_log_out(): void
    {
        $staff = $this->createStaffUser();
        $player = $this->createNormalUser();

        $player->ban('Verbannen wegens spam', null, $staff);
        $this->assertTrue($player->is_banned);

        $logoutResponse = $this->actingAs($player)->post(route('logout'));
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();
    }
}
