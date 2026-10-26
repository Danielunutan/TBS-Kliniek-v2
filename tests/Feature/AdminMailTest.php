<?php

namespace Tests\Feature;

use App\Mail\AdminCustomMail;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminMailTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 500,
            'bank' => 1000,
            'stars' => 10,
            'respect' => 10,
        ], $attrs));
    }

    public function test_guests_cannot_access_owner_mail(): void
    {
        $response = $this->get(route('admin.mail.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_players_get_forbidden(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_staff' => false,
        ]);
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('admin.mail.index'));

        $response->assertStatus(403);
    }

    public function test_staff_moderators_who_are_not_owner_get_forbidden(): void
    {
        $user = User::factory()->create([
            'role' => 'moderator',
            'is_staff' => true,
        ]);
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('admin.mail.index'));

        $response->assertStatus(403);
    }

    public function test_owner_can_access_mail_dashboard(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'daniel@tbs-kliniek.nl',
            'is_staff' => true,
        ]);
        $this->createCharacter($owner);

        $response = $this->actingAs($owner)->get(route('admin.mail.index'));

        $response->assertStatus(200);
        $response->assertSee('TBS Kliniek Mailbox &amp; Brevo E-mailbeheer', false);
        $response->assertSee('vragen@tbs-kliniek.nl');
        $response->assertSee('privacy@tbs-kliniek.nl');
        $response->assertSee('daniel@tbs-kliniek.nl');
    }

    public function test_owner_can_send_mail_via_brevo_and_logs_entry(): void
    {
        Mail::fake();

        $owner = User::factory()->create([
            'role' => 'owner',
            'is_staff' => true,
        ]);
        $this->createCharacter($owner);

        $response = $this->actingAs($owner)->post(route('admin.mail.send'), [
            'sender_email' => 'vragen@tbs-kliniek.nl',
            'recipient_email' => 'speler@example.com',
            'recipient_name' => 'Jan Testspeler',
            'subject' => 'Antwoord op jouw kliniek vraag',
            'body' => 'Beste Jan, dit is een testbericht vanuit het beheerpaneel.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(AdminCustomMail::class, function ($mail) {
            return $mail->hasTo('speler@example.com')
                && $mail->senderEmail === 'vragen@tbs-kliniek.nl'
                && $mail->mailSubject === 'Antwoord op jouw kliniek vraag';
        });

        $this->assertDatabaseHas('admin_mail_logs', [
            'user_id' => $owner->id,
            'sender_email' => 'vragen@tbs-kliniek.nl',
            'recipient_email' => 'speler@example.com',
            'recipient_name' => 'Jan Testspeler',
            'subject' => 'Antwoord op jouw kliniek vraag',
            'status' => 'sent',
        ]);
    }
}
