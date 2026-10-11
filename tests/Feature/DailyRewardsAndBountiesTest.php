<?php

namespace Tests\Feature;

use App\Models\Bounty;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRewardsAndBountiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(string $name, array $attributes = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 5000,
            'bank' => 0,
            'bullets' => 10,
            'diamonds' => 0,
            'health' => 100,
            'energy' => 100,
            'power' => 10,
            'defense' => 10,
            'level' => 1,
            'xp' => 0,
        ], $attributes));
    }

    public function test_daily_rewards_page_loads_successfully(): void
    {
        $character = $this->createCharacter('TestPlayer');

        $response = $this->actingAs($character->user)->get(route('daily.index'));
        $response->assertStatus(200);
        $response->assertSee('Rad van Fortuin');
        $response->assertSee('7-Dagen Login Kalender');
    }

    public function test_daily_spin_awards_prize_and_blocks_second_spin_for_regular_user(): void
    {
        $character = $this->createCharacter('SpontaneousSpinner', [
            'cash' => 1000,
            'bullets' => 0,
            'is_vip' => false,
        ]);

        // First spin succeeds
        $response = $this->actingAs($character->user)->postJson(route('daily.spin'));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $character->refresh();
        $this->assertEquals(now()->toDateString(), $character->last_daily_spin_at->toDateString());
        $this->assertEquals(1, $character->daily_spins_today);

        // Second spin on the same day is blocked with 422
        $secondResponse = $this->actingAs($character->user)->postJson(route('daily.spin'));
        $secondResponse->assertStatus(422);
        $secondResponse->assertJsonPath('success', false);
    }

    public function test_vip_user_can_spin_twice_per_day(): void
    {
        $character = $this->createCharacter('VipSpinner', [
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(7),
        ]);

        // First spin
        $res1 = $this->actingAs($character->user)->postJson(route('daily.spin'));
        $res1->assertStatus(200);
        $res1->assertJsonPath('success', true);

        // Second spin succeeds for VIP
        $res2 = $this->actingAs($character->user)->postJson(route('daily.spin'));
        $res2->assertStatus(200);
        $res2->assertJsonPath('success', true);

        $character->refresh();
        $this->assertEquals(2, $character->daily_spins_today);

        // Third spin is blocked
        $res3 = $this->actingAs($character->user)->postJson(route('daily.spin'));
        $res3->assertStatus(422);
        $res3->assertJsonPath('success', false);
    }

    public function test_streak_claim_advances_day_and_gives_reward(): void
    {
        $character = $this->createCharacter('Streaker', [
            'cash' => 1000,
            'streak_days' => 0,
            'last_streak_claim_at' => null,
        ]);

        $response = $this->actingAs($character->user)->post(route('daily.claim-streak'));
        $response->assertRedirect(route('daily.index'));

        $character->refresh();
        $this->assertEquals(1, $character->streak_days);
        $this->assertEquals(now()->toDateString(), $character->last_streak_claim_at->toDateString());
        $this->assertEquals(2500, $character->cash); // 1000 + 1500 (Day 1 reward)

        // Claiming again today fails
        $secondResponse = $this->actingAs($character->user)->post(route('daily.claim-streak'));
        $secondResponse->assertSessionHas('error');
    }

    public function test_wheel_sectors_contain_vault_codes_masks_and_koffers_and_no_stars(): void
    {
        $character = $this->createCharacter('ItemSpinner');
        $response = $this->actingAs($character->user)->get(route('daily.index'));
        $response->assertStatus(200);

        /** @var array $sectors */
        $sectors = $response->viewData('sectors');
        $types = array_column($sectors, 'type');

        // Verify NO stars
        $this->assertNotContains('stars', $types);

        // Verify new reward types are present
        $this->assertContains('vault_code', $types);
        $this->assertContains('masks', $types);
        $this->assertContains('koffer', $types);
        $this->assertContains('cash', $types);
    }

    public function test_streak_day_three_awards_masks(): void
    {
        $character = $this->createCharacter('MaskCollector', [
            'cash' => 1000,
            'streak_days' => 2,
            'masks' => 0,
            'last_streak_claim_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($character->user)->post(route('daily.claim-streak'));
        $response->assertRedirect(route('daily.index'));

        $character->refresh();
        $this->assertEquals(3, $character->streak_days);
        $this->assertEquals(2, $character->masks);
    }

    public function test_streak_day_four_awards_xxl_case(): void
    {
        $character = $this->createCharacter('CaseCollector', [
            'cash' => 1000,
            'streak_days' => 3,
            'xxl_cases' => 0,
            'last_streak_claim_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($character->user)->post(route('daily.claim-streak'));
        $response->assertRedirect(route('daily.index'));

        $character->refresh();
        $this->assertEquals(4, $character->streak_days);
        $this->assertEquals(1, $character->xxl_cases);
    }

    public function test_streak_day_five_awards_vault_tokens(): void
    {
        $character = $this->createCharacter('VaultCollector', [
            'cash' => 1000,
            'streak_days' => 4,
            'vault_tokens' => 0,
            'last_streak_claim_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($character->user)->post(route('daily.claim-streak'));
        $response->assertRedirect(route('daily.index'));

        $character->refresh();
        $this->assertEquals(5, $character->streak_days);
        $this->assertEquals(1, $character->vault_tokens);
    }

    public function test_bounty_can_be_placed_and_deducts_cash(): void
    {
        $charA = $this->createCharacter('TargetPlacer', [
            'cash' => 25000,
        ]);
        $charB = $this->createCharacter('Scarface', [
            'cash' => 1000,
        ]);

        $response = $this->actingAs($charA->user)->post(route('bounties.store'), [
            'target_name' => 'Scarface',
            'amount' => 10000,
            'reason' => 'Heeft me bestolen!',
        ]);

        $response->assertRedirect(route('bounties.index'));
        $response->assertSessionHas('success');

        $charA->refresh();
        $this->assertEquals(15000, $charA->cash);

        $bounty = Bounty::where('target_character_id', $charB->id)->first();
        $this->assertNotNull($bounty);
        $this->assertEquals(10000, $bounty->amount);
        $this->assertEquals($charA->id, $bounty->placed_by_character_id);
        $this->assertFalse($bounty->is_claimed);
    }

    public function test_bounty_is_claimed_when_target_is_defeated_in_combat(): void
    {
        $hunter = $this->createCharacter('BountyHunter', [
            'cash' => 5000,
            'energy' => 50,
            'strength' => 1000,
            'defense' => 1000,
            'health' => 100,
        ]);

        $target = $this->createCharacter('Outlaw', [
            'strength' => 1,
            'defense' => 1,
            'health' => 100,
            'cash' => 0,
        ]);

        $informer = $this->createCharacter('InformerGuy', [
            'cash' => 1000,
        ]);

        // Place bounty on target
        $bounty = Bounty::create([
            'target_character_id' => $target->id,
            'placed_by_character_id' => $informer->id,
            'amount' => 25000,
            'reason' => 'Gezocht dood of levend',
            'is_claimed' => false,
        ]);

        // Hunter attacks target
        $response = $this->actingAs($hunter->user)->post(route('fight.attack'), [
            'opponent_id' => $target->id,
        ]);

        $bounty->refresh();
        $hunter->refresh();

        $this->assertTrue($bounty->is_claimed);
        $this->assertEquals($hunter->id, $bounty->claimed_by_character_id);
        // Hunter receives initial 5000 + 25000 bounty = 30000
        $this->assertGreaterThanOrEqual(30000, $hunter->cash);
    }
}
