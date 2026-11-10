<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\RookieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RookieQuestAndTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_rookie_quests_are_calculated_correctly(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'RookieJan',
            'level' => 1,
            'cash' => 200,
            'bank' => 600, // meets bank_deposit
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'fights_won' => 0,
            'thefts_succeeded' => 2, // meets first_theft
            'ads_watched_total' => 0,
            'is_onboarded' => true,
        ]);

        $rookieService = app(RookieService::class);
        $data = $rookieService->getQuests($character);

        $this->assertTrue($data['tasks']['first_theft']['is_met']);
        $this->assertTrue($data['tasks']['bank_deposit']['is_met']);
        $this->assertFalse($data['tasks']['training']['is_met']);
        $this->assertFalse($data['tasks']['first_fight']['is_met']);
        $this->assertEquals(2, $data['completed_count']);
        $this->assertFalse($data['all_completed']);
    }

    public function test_player_can_claim_completed_rookie_reward(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'ClaimKlaas',
            'level' => 1,
            'cash' => 500,
            'bank' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'thefts_succeeded' => 1,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('rookie.claim', 'first_theft'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals(1500, $character->cash); // 500 + 1000
        $this->assertContains('first_theft', $character->perks['rookie_rewards_claimed'] ?? []);

        // Cannot claim twice
        $secondResponse = $this->actingAs($user)->postJson(route('rookie.claim', 'first_theft'));
        $secondResponse->assertJson(['success' => false]);
    }

    public function test_player_cannot_claim_unmet_task(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'UnmetPiet',
            'level' => 1,
            'cash' => 100,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'thefts_succeeded' => 0,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('rookie.claim', 'first_theft'));
        $response->assertJson(['success' => false]);
    }

    public function test_grand_completion_bonus_granted_when_all_claimed(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'AllStarInmate',
            'level' => 2,
            'cash' => 1000,
            'bank' => 1000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 12,
            'defense' => 12,
            'stamina' => 12,
            'thefts_succeeded' => 5,
            'fights_won' => 2,
            'ads_watched_total' => 1,
            'is_onboarded' => true,
            'perks' => [
                'rookie_rewards_claimed' => ['first_theft', 'bank_deposit', 'training', 'first_fight'],
            ],
        ]);

        // Claim the 5th and final task
        $response = $this->actingAs($user)->postJson(route('rookie.claim', 'sponsor_vip'));
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'grand_unlocked' => true,
        ]);

        $character->refresh();
        $this->assertTrue((bool) ($character->perks['rookie_grand_claimed'] ?? false));
        $this->assertEquals('Kliniek Overlever', $character->title);
    }

    public function test_intake_tour_can_be_completed_and_awards_bonus(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'TourNewbie',
            'level' => 1,
            'cash' => 250,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('rookie.tour.complete'));
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals(1250, $character->cash);
        $this->assertTrue((bool) ($character->perks['intake_tour_completed'] ?? false));
    }

    public function test_faq_page_renders_with_interactive_tools(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'WikiReader',
            'level' => 1,
            'cash' => 250,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get(route('faq'));
        $response->assertOk();
        $response->assertSee('Interactieve Gevecht Simulator');
        $response->assertSee('Snelle Probleemoplosser voor Gevangenen');
    }

    public function test_bank_deposit_task_remains_met_after_withdrawal_due_to_transaction_or_perk(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'BankTester',
            'level' => 1,
            'cash' => 1000,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'is_onboarded' => true,
        ]);

        $economyService = app(\App\Services\EconomyService::class);
        $economyService->deposit($character, 500);

        // Withdraw back to 0
        $character->refresh();
        $this->assertEquals(500, $character->bank);
        $character->cash += $character->bank;
        $character->bank = 0;
        $character->save();

        $rookieService = app(RookieService::class);
        $this->assertTrue($rookieService->isTaskConditionMet($character, 'bank_deposit'));

        // Can claim successfully even with 0 current bank balance
        $response = $this->actingAs($user)->postJson(route('rookie.claim', 'bank_deposit'));
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
