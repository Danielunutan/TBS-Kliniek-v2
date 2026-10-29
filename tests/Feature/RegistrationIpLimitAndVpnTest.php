<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\VpnDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationIpLimitAndVpnTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_blocked_when_vpn_is_detected(): void
    {
        $mockVpn = $this->createMock(VpnDetectionService::class);
        $mockVpn->method('check')->willReturn([
            'is_vpn' => true,
            'provider' => 'NordVPN Test',
            'country' => 'NL',
        ]);
        $this->app->instance(VpnDetectionService::class, $mockVpn);

        $response = $this->post(route('register.post'), [
            'username' => 'VpnTester',
            'email' => 'vpntester@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['username']);
        $this->assertDatabaseMissing('users', ['email' => 'vpntester@example.com']);
    }

    public function test_registration_allows_up_to_two_accounts_per_ip_and_blocks_third(): void
    {
        $mockVpn = $this->createMock(VpnDetectionService::class);
        $mockVpn->method('check')->willReturn([
            'is_vpn' => false,
            'provider' => null,
            'country' => 'NL',
        ]);
        $this->app->instance(VpnDetectionService::class, $mockVpn);

        $ip = '198.51.100.42';

        // Account 1
        $response1 = $this->withServerVariables(['REMOTE_ADDR' => $ip])->post(route('register.post'), [
            'username' => 'FamilyMember1',
            'email' => 'family1@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);
        $response1->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'family1@example.com', 'registration_ip' => $ip]);

        auth()->logout();
        session()->flush();

        // Account 2
        $response2 = $this->withServerVariables(['REMOTE_ADDR' => $ip])->post(route('register.post'), [
            'username' => 'FamilyMember2',
            'email' => 'family2@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);
        $response2->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'family2@example.com', 'registration_ip' => $ip]);

        auth()->logout();
        session()->flush();

        // Account 3 (Should be BLOCKED)
        $response3 = $this->withServerVariables(['REMOTE_ADDR' => $ip])->post(route('register.post'), [
            'username' => 'FamilyMember3',
            'email' => 'family3@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);
        $response3->assertSessionHasErrors(['username']);
        $this->assertDatabaseMissing('users', ['email' => 'family3@example.com']);
    }

    public function test_exempt_accounts_do_not_count_towards_ip_limit(): void
    {
        $mockVpn = $this->createMock(VpnDetectionService::class);
        $mockVpn->method('check')->willReturn([
            'is_vpn' => false,
            'provider' => null,
            'country' => 'NL',
        ]);
        $this->app->instance(VpnDetectionService::class, $mockVpn);

        $ip = '203.0.113.88';

        // Create 2 existing users on this IP, but 1 is marked as exempt by admin
        User::factory()->create(['registration_ip' => $ip, 'ip_limit_exempt' => true]);
        User::factory()->create(['registration_ip' => $ip, 'ip_limit_exempt' => false]);

        // Registration should succeed because only 1 non-exempt account exists
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])->post(route('register.post'), [
            'username' => 'ExemptFamilyTest',
            'email' => 'exemptfamily@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'exemptfamily@example.com', 'registration_ip' => $ip]);
    }
}
