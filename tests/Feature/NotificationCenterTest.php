<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_players_can_view_their_notifications_without_seeing_those_of_others(): void
    {
        $character = Character::factory()->create();
        $other = Character::factory()->create();
        GameNotification::withoutEvents(function () use ($character, $other): void {
            GameNotification::create(['character_id' => $character->id, 'type' => 'gang', 'title' => 'Bendeloon ontvangen', 'message' => 'Je loon staat klaar.', 'is_read' => false]);
            GameNotification::create(['character_id' => $other->id, 'type' => 'gang', 'title' => 'Privébericht andere patiënt', 'message' => 'Niet zichtbaar.', 'is_read' => false]);
        });

        $this->actingAs($character->user)->get('/meldingen')
            ->assertOk()->assertSee('Bendeloon ontvangen')->assertSee('Je loon staat klaar.')
            ->assertDontSee('Privébericht andere patiënt');
        $this->assertDatabaseHas('game_notifications', ['character_id' => $character->id, 'title' => 'Bendeloon ontvangen', 'is_read' => false]);
    }

    public function test_marking_notifications_read_only_affects_the_current_player(): void
    {
        $character = Character::factory()->create();
        $other = Character::factory()->create();
        GameNotification::withoutEvents(function () use ($character, $other): void {
            foreach ([$character, $other] as $recipient) {
                GameNotification::create(['character_id' => $recipient->id, 'type' => 'gang', 'title' => 'Bendeloon', 'message' => 'Je loon is uitbetaald.', 'is_read' => false]);
            }
        });

        $this->actingAs($character->user)->post('/meldingen/alles-lezen')->assertRedirect('/meldingen');
        $this->assertDatabaseHas('game_notifications', ['character_id' => $character->id, 'is_read' => true]);
        $this->assertDatabaseHas('game_notifications', ['character_id' => $other->id, 'is_read' => false]);
    }

    public function test_both_bells_and_realtime_count_reflect_unread_notifications(): void
    {
        $character = Character::factory()->create();
        GameNotification::withoutEvents(function () use ($character): void {
            GameNotification::create(['character_id' => $character->id, 'type' => 'gang', 'title' => 'Bendeloon', 'message' => 'Je loon is uitbetaald.', 'is_read' => false]);
            GameNotification::create(['character_id' => $character->id, 'type' => 'gang', 'title' => 'Bendebericht', 'message' => 'Er is nieuws van je bende.', 'is_read' => true]);
        });

        $this->actingAs($character->user)->get('/meldingen')
            ->assertOk()->assertSee('unreadNotifications: 1', false)
            ->assertSee('Bekijk meldingen', false);
        $this->assertSame(2, substr_count($this->get('/meldingen')->getContent(), 'title="Bekijk meldingen"'));
        $this->getJson(route('character.realtime-stats'))->assertOk()->assertJsonPath('unread_notifications', 1);
        $this->post('/meldingen/alles-lezen')->assertRedirect('/meldingen');
        $this->getJson(route('character.realtime-stats'))->assertOk()->assertJsonPath('unread_notifications', 0);
    }

    public function test_notifications_are_paginated_and_content_is_escaped(): void
    {
        $character = Character::factory()->create();
        GameNotification::withoutEvents(function () use ($character): void {
            for ($i = 1; $i <= 21; $i++) {
                GameNotification::create([
                    'character_id' => $character->id,
                    'type' => 'gang',
                    'title' => 'Afdelingsbericht '.$i,
                    'message' => $i === 21 ? '<script>gevaarlijk</script>' : 'Nieuws uit de kliniek.',
                    'is_read' => false,
                ]);
            }
        });

        $this->actingAs($character->user)->get('/meldingen')
            ->assertOk()->assertSee('Afdelingsbericht 21')->assertDontSee('Afdelingsbericht 1</h2>', false)
            ->assertSee('<script>gevaarlijk</script>')->assertDontSee('<script>gevaarlijk</script>', false);
        $this->get('/meldingen?page=2')->assertOk()->assertSee('Afdelingsbericht 1');
    }

    public function test_empty_notifications_and_guest_access(): void
    {
        $this->get('/meldingen')->assertRedirect();
        $character = Character::factory()->create();
        $this->actingAs($character->user)->get('/meldingen')->assertOk()->assertSee('Je hebt nog geen meldingen');
    }
}
