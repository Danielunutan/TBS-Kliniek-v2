<?php

namespace Tests\Feature;

use App\Mail\VerificationIssueFixedMail;
use App\Mail\VerifyEmailCodeMail;
use App\Models\Character;
use App\Models\User;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(User $user, bool $onboarded = true): Character
    {
        return Character::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'title' => 'Patiënt',
            'avatar' => 'assets/characters/avatar_male_1.jpg',
            'gender' => 'male',
            'bio' => 'Test bio',
            'status_message' => 'Test status',
            'level' => 1,
            'xp' => 0,
            'total_xp' => 0,
            'cash' => 250,
            'bank' => 1000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'respect' => 10,
            'is_onboarded' => $onboarded,
        ]);
    }

    public function test_registration_generates_verification_code_and_sends_custom_email(): void
    {
        Mail::fake();

        $turnstile = $this->createStub(TurnstileService::class);
        $turnstile->method('verify')->willReturn(true);
        $this->app->instance(TurnstileService::class, $turnstile);

        $response = $this->post('/register', [
            'username' => 'TestPatient123',
            'email' => 'patient@tbskliniek.nl',
            'password' => 'wachtwoord123',
            'password_confirmation' => 'wachtwoord123',
            'gender' => 'male',
            'avatar' => 'assets/characters/avatar_male_1.jpg',
            'terms' => '1',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertAuthenticated();

        $user = User::where('email', 'patient@tbskliniek.nl')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verification_code);
        $this->assertEquals(6, strlen($user->email_verification_code));
        $this->assertTrue(is_numeric($user->email_verification_code));
        $this->assertNotNull($user->email_verification_code_expires_at);
        $this->assertTrue($user->email_verification_code_expires_at->isFuture());

        Mail::assertSent(VerifyEmailCodeMail::class, function (VerifyEmailCodeMail $mail) use ($user) {
            return $mail->hasTo('patient@tbskliniek.nl') &&
                   $mail->code === $user->email_verification_code;
        });
    }

    public function test_verify_notice_page_loads_with_code_form(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'email_verification_code' => '654321',
            'email_verification_code_expires_at' => now()->addMinutes(30),
        ]);

        $this->createCharacter($user);

        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('Voer je Verificatiecode in');
        $response->assertSee('name="code"', false);
    }

    public function test_submitting_incorrect_code_fails(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'email_verification_code' => '112233',
            'email_verification_code_expires_at' => now()->addMinutes(30),
        ]);

        $this->createCharacter($user);

        $response = $this->actingAs($user)->post('/email/verify/code', [
            'code' => '999999',
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_submitting_expired_code_fails(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'email_verification_code' => '123456',
            'email_verification_code_expires_at' => now()->subMinutes(1), // Expired!
        ]);

        $this->createCharacter($user);

        $response = $this->actingAs($user)->post('/email/verify/code', [
            'code' => '123456',
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_submitting_correct_code_verifies_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'email_verification_code' => '482915',
            'email_verification_code_expires_at' => now()->addMinutes(25),
        ]);

        $this->createCharacter($user, true);

        $response = $this->actingAs($user)->post('/email/verify/code', [
            'code' => '482915',
        ]);

        $response->assertRedirect('/dashboard');

        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser->email_verified_at);
        $this->assertNull($freshUser->email_verification_code);
        $this->assertNull($freshUser->email_verification_code_expires_at);
    }

    public function test_resend_verification_generates_new_code_and_sends_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'email_verification_code' => '111111',
            'email_verification_code_expires_at' => now()->subMinutes(10),
        ]);

        $this->createCharacter($user);

        $response = $this->actingAs($user)->post('/email/verification-notification');
        $response->assertSessionHas('status', 'verification-code-sent');

        $freshUser = $user->fresh();
        $this->assertNotEquals('111111', $freshUser->email_verification_code);
        $this->assertTrue($freshUser->email_verification_code_expires_at->isFuture());

        Mail::assertSent(VerifyEmailCodeMail::class, function (VerifyEmailCodeMail $mail) use ($freshUser) {
            return $mail->code === $freshUser->email_verification_code;
        });
    }

    public function test_verification_issue_fixed_mail_renders_and_sends(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $code = '839201';
        $expiresAt = now()->addMinutes(30);

        Mail::to($user->email)->send(new VerificationIssueFixedMail($user, $code, $expiresAt));

        Mail::assertSent(VerificationIssueFixedMail::class, function (VerificationIssueFixedMail $mail) use ($code) {
            return $mail->code === $code;
        });
    }
}
