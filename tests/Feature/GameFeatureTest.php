<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\Mission;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\EconomyService;
use App\Services\LevelService;
use App\Services\MissionService;
use App\Services\PrisonService;
use App\Services\TheftService;
use App\Services\TrainingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_page_loads_with_tbs_kliniek_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('TBS', false);
        $response->assertSee('KLINIEK', false);
        $response->assertSee('Henkie', false);
    }

    public function test_demo_login_authenticates_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/demo-login');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Henkie');
        $dashboardResponse->assertSee('Dashboard');
    }

    public function test_level_service_calculates_and_processes_rollover_xp(): void
    {
        $levelService = app(LevelService::class);
        $character = Character::first();
        $character->update(['xp' => 0]);

        $initialLevel = $character->level;
        $reqXp = $levelService->getXpRequiredForNextLevel($initialLevel);

        $result = $levelService->addXp($character, $reqXp + 50);

        $this->assertTrue($result['leveled_up']);
        $this->assertEquals($initialLevel + 1, $character->level);
        $this->assertEquals(50, $character->xp);
    }

    public function test_economy_service_deposits_and_withdraws_safely(): void
    {
        $economy = app(EconomyService::class);
        $character = Character::first();

        $character->update(['cash' => 500, 'bank' => 1000]);

        $this->assertTrue($economy->deposit($character, 200));
        $this->assertEquals(300, $character->cash);
        $this->assertEquals(1200, $character->bank);

        $this->assertTrue($economy->withdraw($character, 500));
        $this->assertEquals(800, $character->cash);
        $this->assertEquals(700, $character->bank);
    }

    public function test_mission_execution_updates_character_stats(): void
    {
        $missionService = app(MissionService::class);
        $character = Character::first();
        $mission = Mission::first();
        $mission->update(['success_rate' => 100]);

        $character->update(['energy' => 100, 'level' => 10]);

        $result = $missionService->execute($character, $mission);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['cash_earned']);
        $this->assertGreaterThan(0, $result['xp_earned']);
    }

    public function test_training_service_increases_attributes(): void
    {
        $trainingService = app(TrainingService::class);
        $character = Character::first();

        $character->update(['strength' => 10, 'cash' => 1000, 'energy' => 100]);

        $result = $trainingService->train($character, 'strength');

        $this->assertTrue($result['success']);
        $this->assertEquals(11, $character->fresh()->strength);
    }

    public function test_posting_guestbook_message_persists(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->post("/speler/{$user->character->name}/gastenboek", [
            'message' => 'Testbericht voor het dossier!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('guestbook_entries', [
            'message' => 'Testbericht voor het dossier!',
        ]);
    }

    public function test_discord_login_creates_and_authenticates_user(): void
    {
        $response = $this->get('/auth/discord');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'discord_inmate@tbskliniek.nl',
        ]);
    }

    public function test_registration_with_custom_username_avatar_gender_and_terms(): void
    {
        $response = $this->post('/register', [
            'username' => 'Samantha_Sluw',
            'email' => 'samantha@tbskliniek.nl',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
            'gender' => 'female',
            'avatar' => 'assets/characters/avatar_female_2.jpg',
            'terms' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('characters', [
            'name' => 'Samantha_Sluw',
            'gender' => 'female',
            'avatar' => 'assets/characters/avatar_female_2.jpg',
            'title' => 'Nieuwe Patiënte',
        ]);
    }

    public function test_registration_fails_without_accepting_terms(): void
    {
        $response = $this->post('/register', [
            'username' => 'Geen_Terms_Kees',
            'email' => 'geen_terms@tbskliniek.nl',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
            'gender' => 'male',
            'avatar' => 'assets/characters/avatar_male_1.jpg',
        ]);

        $response->assertSessionHasErrors(['terms']);
        $this->assertGuest();
    }

    public function test_messaging_system_delivers_and_deletes_letters_between_inmates(): void
    {
        $sender = User::where('email', 'demo@tbskliniek.nl')->first();
        $recipient = User::create([
            'name' => 'Willem',
            'email' => 'willem@tbskliniek.nl',
            'password' => bcrypt('password'),
        ]);
        $recipientChar = Character::create([
            'user_id' => $recipient->id,
            'name' => 'Willem_De_Sluwe',
            'title' => 'Patiënt',
            'avatar' => 'characters/henkie.jpg',
            'gender' => 'male',
            'level' => 1,
            'cash' => 1000,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'speed' => 10,
            'intellect' => 10,
            'respect' => 10,
        ]);

        $this->actingAs($sender);

        $sendResponse = $this->post(route('messages.send'), [
            'recipient_name' => 'Willem_De_Sluwe',
            'subject' => 'Plannetje voor de luchtplaats',
            'content' => 'Hey Willem, heb jij nog wat rookwaar liggen voor vanavond?',
        ]);

        $sendResponse->assertSessionHas('success');

        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $recipientChar->id,
            'sender_id' => $sender->character->id,
            'subject' => 'Plannetje voor de luchtplaats',
            'type' => 'player',
        ]);

        $this->actingAs($recipient);
        $inboxResponse = $this->get('/berichten');
        $inboxResponse->assertStatus(200);
        $inboxResponse->assertSee('Plannetje voor de luchtplaats');

        $message = GameMessage::where('recipient_id', $recipientChar->id)->first();
        $deleteResponse = $this->delete(route('messages.destroy', $message));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('game_messages', ['id' => $message->id]);
    }

    public function test_vault_tokens_can_buy_code_and_claim_level_milestone_rewards(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['vault_tokens' => 10, 'level' => 5, 'claimed_level_rewards' => []]);

        $this->actingAs($user);

        // Buy code
        $buyResponse = $this->post(route('vault.buy-code'));
        $buyResponse->assertSessionHas('vault_revealed_code');
        $char->refresh();
        $this->assertEquals(6, $char->vault_tokens); // 10 - 4

        // Visiting vault dispatches automated milestone codes via messages
        $this->get(route('vault.index'));
        $char->refresh();
        $this->assertContains(3, $char->claimed_level_rewards);

        // Find the generated milestone code and redeem it
        $vaultCode = VaultCode::where('character_id', $char->id)->first();
        $this->assertNotNull($vaultCode);

        $redeemResponse = $this->post(route('vault.redeem'), ['code' => $vaultCode->code]);
        $redeemResponse->assertSessionHas('success');
        $char->refresh();
        $this->assertGreaterThan(6, $char->vault_tokens);
        $this->assertGreaterThan(0, $char->bank);
    }

    public function test_pvp_theft_deducts_cash_from_victim_and_dispatches_alarm_message(): void
    {
        $thiefUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $victimUser = User::create([
            'name' => 'Slachtoffer',
            'email' => 'slachtoffer@tbskliniek.nl',
            'password' => bcrypt('password'),
        ]);
        $victimChar = Character::create([
            'user_id' => $victimUser->id,
            'name' => 'Rijk_Slachtoffer',
            'title' => 'Patiënt',
            'avatar' => 'characters/henkie.jpg',
            'gender' => 'male',
            'level' => 2,
            'cash' => 5000,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 5,
            'defense' => 5,
            'speed' => 5,
            'intellect' => 5,
            'respect' => 5,
        ]);

        $thiefChar = $thiefUser->character;
        $thiefChar->update(['energy' => 100, 'cash' => 0, 'is_in_prison' => false, 'intelligence' => 500, 'stamina' => 500]);

        $theftService = app(TheftService::class);
        $result = $theftService->stealFromInmate($thiefChar, $victimChar);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['cash_stolen']);
        $this->assertLessThan(5000, $victimChar->fresh()->cash);

        // Check that victim received alarm message
        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $victimChar->id,
            'sender_id' => $thiefChar->id,
            'type' => 'theft',
        ]);
    }

    public function test_prison_service_sends_automated_incident_message_when_jailed(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        $prisonService = app(PrisonService::class);
        $prisonService->jail($char, 10, 'Betrapt bij diefstal van medicijnen');

        $this->assertTrue($char->fresh()->is_in_prison);
        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $char->id,
            'type' => 'jail',
            'subject' => '🚨 Arrestatierapport: Isoleercel Straf',
        ]);
    }

    public function test_leaderboard_page_loads_with_character_rankings(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get('/ranglijst');

        $response->assertStatus(200);
        $response->assertSee('Patiënten Ranglijst');
        $response->assertSee($user->character->name);
    }
}
