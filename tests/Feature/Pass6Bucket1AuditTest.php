<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\Friend;
use App\Models\GameSetting;
use App\Models\Gang;
use App\Models\Loan;
use App\Models\SmuggleDropping;
use App\Models\User;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket1AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_connect_four_records_today_respect_when_respect_is_awarded(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'respect' => 24,
            'today_respect' => 0,
            'today_respect_date' => null,
            'cash' => 1000,
        ]);

        $response = $this->actingAs($user)->postJson(route('connect_four.finish'), [
            'winner' => 'player',
            'moves' => 8,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $character->refresh();
        $this->assertSame(27, $character->respect);
        $this->assertSame(3.0, (float) $character->today_respect);
        $this->assertSame(now()->format('Y-m-d'), $character->today_respect_date);
    }

    public function test_connect_four_skip_cooldown_with_stars_resets_cooldown(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'stars' => 20,
        ]);

        CharacterConnectFourStat::create([
            'character_id' => $character->id,
            'matches_played' => 5,
            'cooldown_until' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('connect_four.skip_cooldown_stars'));
        $response->assertRedirect(route('connect_four.index'));

        $character->refresh();
        $this->assertSame(10, $character->stars);
        $stat = CharacterConnectFourStat::where('character_id', $character->id)->firstOrFail();
        $this->assertFalse($stat->isOnCooldown());
    }

    public function test_friend_controller_formats_cooldown_hours_as_integer_and_handles_add(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'PatientAlpha',
            'last_friends_mass_message_at' => now()->subMinutes(35),
        ]);

        $targetUser = User::factory()->create(['is_onboarded' => true]);
        $target = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'PatientBeta',
        ]);

        $this->actingAs($user)->post(route('friends.add'), [
            'friend_name' => $target->name,
        ])->assertRedirect();

        $this->assertSame(1, Friend::where('character_id', $character->id)->where('friend_id', $target->id)->count());

        $response = $this->actingAs($user)->post(route('friends.mass-message'), [
            'subject' => 'Test',
            'message' => 'Test bericht',
        ]);
        $response->assertSessionHas('error', '⏳ Massabericht naar vrienden kan maar 1x per 24 uur verzonden worden. Wacht nog 24 uur.');
    }

    public function test_dropping_controller_consumes_energy_atomically_and_sets_cooldown(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 5,
            'energy' => 50,
            'max_energy' => 100,
            'cash' => 2000,
        ]);

        $this->actingAs($user)->post(route('droppings.tennisball'))->assertRedirect();
        $character->refresh();
        $this->assertSame(35, $character->energy);
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($character, 'dropping_tennisball'));
        $dropping = SmuggleDropping::where('character_id', $character->id)->latest('id')->firstOrFail();
        $this->assertSame($dropping->xp_gained, $character->xp);
    }

    public function test_arm_wrestling_finish_npc_consumes_energy_and_awards_respect_via_add_respect(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'energy' => 50,
            'max_energy' => 100,
            'cash' => 5000,
            'respect' => 10,
            'today_respect' => 0,
            'today_respect_date' => null,
            'strength' => 50,
        ]);

        $response = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'duration_ms' => 2000,
            'clicks' => 15,
        ]);

        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('won', true);
        $character->refresh();
        $this->assertSame(45, $character->energy);
        $this->assertTrue($character->respect > 10);
        $this->assertTrue($character->today_respect > 0);
        $this->assertSame(now()->format('Y-m-d'), $character->today_respect_date);
    }

    public function test_bank_repay_loan_blocked_when_imprisoned_or_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
        ]);

        $loan = Loan::create([
            'loan_number' => 'LN-12345',
            'character_id' => $character->id,
            'amount' => 5000,
            'interest_rate' => 5.0,
            'interest_amount' => 250,
            'total_due' => 5250,
            'total_paid' => 0,
            'remaining_due' => 5250,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('bank.loans.repay', $loan), [
            'amount' => 500,
            'source' => 'cash',
        ]);
        $response->assertRedirect(route('prison'));

        // Test coma
        $character->update(['is_in_prison' => false, 'prison_until' => null, 'coma_until' => now()->addMinutes(30)]);
        $response = $this->actingAs($user)->post(route('bank.loans.repay-full', $loan), [
            'source' => 'cash',
        ]);
        $response->assertSessionHas('error');
    }

    public function test_casino_play_uses_game_setting_win_rate(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
        ]);

        GameSetting::set('win_rate_casino', 100);

        $response = $this->actingAs($user)->postJson(route('casino.play'), [
            'game' => 'dice',
            'bet' => 100,
        ]);

        $response->assertOk()->assertJsonPath('win', true);
        $this->assertStringContainsString('dobbelstenen', $response->json('message'));

        // Now with 0% win rate
        $character->refresh();
        app(CooldownService::class)->clearCooldown($character, 'casino_dice');
        GameSetting::set('win_rate_casino', 0);

        $response = $this->actingAs($user)->postJson(route('casino.play'), [
            'game' => 'dice',
            'bet' => 100,
        ]);

        $response->assertOk()->assertJsonPath('win', false);
    }

    public function test_fight_resolve_arena_updates_today_respect(): void
    {
        $attackerUser = User::factory()->create(['is_onboarded' => true]);
        $attacker = Character::factory()->create([
            'user_id' => $attackerUser->id,
            'respect' => 10,
            'today_respect' => 0,
            'today_respect_date' => null,
            'health' => 100,
        ]);

        $defenderUser = User::factory()->create(['is_onboarded' => true]);
        $defender = Character::factory()->create([
            'user_id' => $defenderUser->id,
            'cash' => 2000,
            'health' => 100,
        ]);

        $battleData = [
            'attacker_id' => $attacker->id,
            'defender_id' => $defender->id,
            'won' => true,
            'stolen_cash' => 500,
            'damage_taken' => 10,
            'xp' => 25,
            'respect' => 2,
            'resolved' => false,
        ];

        $response = $this->actingAs($attackerUser)
            ->withSession(['pending_arena_battle' => $battleData])
            ->postJson(route('fight.arena.resolve', $defender));

        $response->assertOk()->assertJsonPath('success', true);
        $attacker->refresh();
        $this->assertSame(12, $attacker->respect);
        $this->assertSame(2.0, (float) $attacker->today_respect);
        $this->assertSame(now()->format('Y-m-d'), $attacker->today_respect_date);
    }

    public function test_gang_donate_stars_updates_today_respect_and_blocks_imprisoned(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $gang = Gang::create([
            'name' => 'Kliniek Test Gang',
            'tag' => 'KTG',
            'specialty' => 'Kantine Controle',
            'leader_character_id' => null,
            'treasury' => 1000,
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'stars' => 10,
            'respect' => 10,
            'today_respect' => 0,
            'today_respect_date' => null,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('gangs.donate-stars'), ['stars' => 2]);
        $response->assertSessionHas('error');

        $character->update(['is_in_prison' => false, 'prison_until' => null]);
        $response = $this->actingAs($user)->post(route('gangs.donate-stars'), ['stars' => 2]);
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertSame(20, $character->respect);
        $this->assertSame(10.0, (float) $character->today_respect);
        $this->assertSame(now()->format('Y-m-d'), $character->today_respect_date);
    }

    public function test_gang_claim_wage_blocked_when_imprisoned(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $gang = Gang::create([
            'name' => 'Wages Test Gang',
            'tag' => 'WTG',
            'specialty' => 'Kantine Controle',
            'treasury' => 50000,
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('gangs.claim-wage'));
        $response->assertSessionHas('error', 'Vanuit de isoleercel kun je geen bende-uurloon opeisen!');
    }

    public function test_combat_training_train_under_transaction_lock(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 1,
            'energy' => 50,
            'cash' => 2000,
            'strength' => 10,
        ]);

        $response = $this->actingAs($user)->post(route('combat_training.train'), [
            'session_id' => 1,
            'mode' => 'stat',
        ]);

        $response->assertRedirect();
        $character->refresh();
        $this->assertSame(11, $character->strength);
        $this->assertSame(40, $character->energy);
        $this->assertSame(1850, $character->cash);
    }

    public function test_battle_pass_claim_updates_today_respect(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 10,
            'respect' => 500,
            'today_respect' => 0,
            'today_respect_date' => null,
        ]);

        $response = $this->actingAs($user)->post(route('battlepass.claim'), [
            'level' => 3,
            'track' => 'free',
        ], ['X-BP-Fast' => '1']);

        $response->assertJsonPath('success', true);
        $character->refresh();
        $this->assertTrue($character->today_respect > 0);
        $this->assertSame(now()->format('Y-m-d'), $character->today_respect_date);
    }

    public function test_admin_controller_update_player_allows_moderator_when_finances_unchanged(): void
    {
        $modUser = User::factory()->create(['is_onboarded' => true, 'role' => 'moderator']);
        $modChar = Character::factory()->create(['user_id' => $modUser->id]);

        $targetUser = User::factory()->create(['is_onboarded' => true, 'role' => 'user']);
        $targetChar = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'OldName',
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 10,
            'respect' => 50,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
        ]);

        $response = $this->actingAs($modUser)->post(route('admin.player.update', $targetChar), [
            'character_name' => 'NewModeratedName',
            'cash' => '1000',
            'bank' => '5000',
            'stars' => '10',
            'respect' => '50',
            'vip_points' => '0',
            'health' => 100,
            'energy' => 100,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $targetChar->refresh();
        $this->assertSame('NewModeratedName', $targetChar->name);
    }
}
