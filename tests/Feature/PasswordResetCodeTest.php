<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(TurnstileService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });
    }

    public function test_user_can_request_password_reset_code(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'speler@example.com',
            'password' => Hash::make('oudeWachtwoord123!'),
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'speler@example.com',
        ]);

        $response->assertRedirect(route('password.reset', ['email' => 'speler@example.com']));

        $user->refresh();
        $this->assertNotNull($user->password_reset_code);
        $this->assertEquals(6, strlen($user->password_reset_code));
        $this->assertNotNull($user->password_reset_code_expires_at);
        $this->assertTrue($user->password_reset_code_expires_at->isFuture());

        Mail::assertSent(PasswordResetCodeMail::class, function ($mail) use ($user) {
            return $mail->hasTo('speler@example.com') && $mail->code === $user->password_reset_code;
        });
    }

    public function test_user_can_reset_password_with_valid_6_digit_code(): void
    {
        $user = User::factory()->create([
            'email' => 'speler@example.com',
            'password' => Hash::make('oudeWachtwoord123!'),
            'password_reset_code' => '482910',
            'password_reset_code_expires_at' => now()->addMinutes(25),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => 'speler@example.com',
            'code' => '482910',
            'password' => 'nieuwWachtwoord2026!',
            'password_confirmation' => 'nieuwWachtwoord2026!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('nieuwWachtwoord2026!', $user->password));
        $this->assertNull($user->password_reset_code);
        $this->assertNull($user->password_reset_code_expires_at);
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'speler@example.com',
            'password' => Hash::make('oudeWachtwoord123!'),
            'password_reset_code' => '123456',
            'password_reset_code_expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => 'speler@example.com',
            'code' => '123456',
            'password' => 'nieuwWachtwoord2026!',
            'password_confirmation' => 'nieuwWachtwoord2026!',
        ]);

        $response->assertSessionHasErrors(['code']);
        $user->refresh();
        $this->assertTrue(Hash::check('oudeWachtwoord123!', $user->password));
    }

    public function test_invalid_code_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'speler@example.com',
            'password' => Hash::make('oudeWachtwoord123!'),
            'password_reset_code' => '999999',
            'password_reset_code_expires_at' => now()->addMinutes(25),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => 'speler@example.com',
            'code' => '000000',
            'password' => 'nieuwWachtwoord2026!',
            'password_confirmation' => 'nieuwWachtwoord2026!',
        ]);

        $response->assertSessionHasErrors(['code']);
        $user->refresh();
        $this->assertTrue(Hash::check('oudeWachtwoord123!', $user->password));
    }
}
