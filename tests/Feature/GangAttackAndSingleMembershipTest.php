<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangAlliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangAttackAndSingleMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_cannot_create_gang_if_already_in_a_gang(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'GangTestChar1',
            'gender' => 'male',
            'level' => 10,
            'stars' => 500,
        ]);

        $existingGang = Gang::create([
            'name' => 'OriginalGang',
            'tag' => 'OG',
            'specialty' => 'Vleugel A',
            'treasury' => 50000,
        ]);

        $character->gang_id = $existingGang->id;
        $character->gang_role = 'member';
        $character->save();

        $this->actingAs($user);

        // Attempt to create another gang while in a gang
        $response = $this->post(route('gangs.create'), [
            'name' => 'SecondGang',
            'tag' => 'SG',
        ]);

        $response->assertSessionHas('error', 'Je bent al lid van een bende. Verlaat eerst je huidige bende.');
        $this->assertDatabaseMissing('gangs', ['name' => 'SecondGang']);

        // Leave gang
        $leaveRes = $this->post(route('gangs.leave'));
        $leaveRes->assertSessionHas('success');

        $character->refresh();
        $this->assertNull($character->gang_id);

        // Now creating a gang succeeds
        $createRes = $this->post(route('gangs.create'), [
            'name' => 'NewGang',
            'tag' => 'NG',
        ]);

        $createRes->assertSessionHas('success');
        $this->assertDatabaseHas('gangs', ['name' => 'NewGang']);
    }

    public function test_regular_member_cannot_attack_rival_gang(): void
    {
        $attackerLeaderUser = User::factory()->create();
        $attackerLeader = Character::create([
            'user_id' => $attackerLeaderUser->id,
            'name' => 'LeaderPatient',
            'gender' => 'female',
            'level' => 12,
        ]);

        $attackerGang = Gang::create([
            'name' => 'AttackerGang',
            'tag' => 'AG',
            'specialty' => 'Vleugel B',
            'leader_character_id' => $attackerLeader->id,
            'attack_power' => 5000,
            'protection_points' => 2000,
            'treasury' => 50000,
        ]);
        $attackerLeader->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        $memberUser = User::factory()->create();
        $member = Character::create([
            'user_id' => $memberUser->id,
            'name' => 'MemberPatient',
            'gender' => 'male',
            'level' => 8,
            'gang_id' => $attackerGang->id,
            'gang_role' => 'member',
        ]);

        $defenderGang = Gang::create([
            'name' => 'DefenderGang',
            'tag' => 'DG',
            'specialty' => 'Kantine',
            'attack_power' => 2000,
            'protection_points' => 3000,
            'treasury' => 100000,
        ]);

        $this->actingAs($memberUser);

        $response = $this->post(route('gangs.attack'), [
            'target_gang_id' => $defenderGang->id,
        ]);

        $response->assertSessionHas('error', 'Alleen bendebeheerders (leider, co-leider, kantine smokkelaar of kasbeheerder) kunnen bende-aanvallen starten!');
        $this->assertDatabaseCount('gang_attacks', 0);
    }

    public function test_leader_can_attack_rival_gang_and_plunder(): void
    {
        $attackerUser = User::factory()->create();
        $attackerLeader = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'OverlordPlayer',
            'gender' => 'male',
            'level' => 20,
            'respect' => 100,
        ]);

        $attackerGang = Gang::create([
            'name' => 'OverlordGang',
            'tag' => 'OG',
            'specialty' => 'Macht',
            'leader_character_id' => $attackerLeader->id,
            'attack_power' => 100000, // Very high power guarantees victory
            'protection_points' => 1000,
            'treasury' => 10000,
        ]);
        $attackerLeader->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        $defenderLeaderUser = User::factory()->create();
        $defenderLeader = Character::create([
            'user_id' => $defenderLeaderUser->id,
            'name' => 'WeakDefender',
            'gender' => 'female',
            'level' => 5,
        ]);

        $defenderGang = Gang::create([
            'name' => 'WeakGang',
            'tag' => 'WG',
            'specialty' => 'Geen',
            'leader_character_id' => $defenderLeader->id,
            'attack_power' => 500,
            'protection_points' => 500,
            'treasury' => 200000,
        ]);
        $defenderLeader->update(['gang_id' => $defenderGang->id, 'gang_role' => 'leader']);

        $this->actingAs($attackerUser);

        $response = $this->post(route('gangs.attack'), [
            'target_gang_id' => $defenderGang->id,
        ]);

        $response->assertSessionHas('success');

        $attackerGang->refresh();
        $defenderGang->refresh();

        $this->assertGreaterThan(10000, $attackerGang->treasury);
        $this->assertLessThan(200000, $defenderGang->treasury);
        $this->assertTrue($defenderGang->isUnderShield());
        $this->assertNotNull($attackerGang->last_attack_at);

        $this->assertDatabaseHas('gang_attacks', [
            'attacker_gang_id' => $attackerGang->id,
            'target_gang_id' => $defenderGang->id,
            'won' => true,
        ]);
    }

    public function test_cannot_attack_ally_gang(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'AllyAttacker',
            'gender' => 'male',
            'level' => 15,
        ]);

        $gang1 = Gang::create([
            'name' => 'AlliedGangOne',
            'tag' => 'A1',
            'specialty' => 'Alliantie 1',
            'leader_character_id' => $attacker->id,
            'attack_power' => 10000,
            'protection_points' => 5000,
        ]);
        $attacker->update(['gang_id' => $gang1->id, 'gang_role' => 'leader']);

        $gang2 = Gang::create([
            'name' => 'AlliedGangTwo',
            'tag' => 'A2',
            'specialty' => 'Alliantie 2',
            'attack_power' => 8000,
            'protection_points' => 6000,
        ]);

        GangAlliance::create([
            'gang_id' => $gang1->id,
            'ally_gang_id' => $gang2->id,
        ]);

        $this->actingAs($attackerUser);

        $response = $this->post(route('gangs.attack'), [
            'target_gang_id' => $gang2->id,
        ]);

        $response->assertSessionHas('error', "{$gang2->name} is een bondgenoot! Verbreek eerst het bondgenootschap om oorlog te voeren.");
    }

    public function test_cannot_attack_gang_with_active_shield(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'WarriorPatient',
            'gender' => 'female',
            'level' => 16,
        ]);

        $gang1 = Gang::create([
            'name' => 'Warriors',
            'tag' => 'WAR',
            'specialty' => 'Strijders',
            'leader_character_id' => $attacker->id,
            'attack_power' => 10000,
        ]);
        $attacker->update(['gang_id' => $gang1->id, 'gang_role' => 'leader']);

        $shieldedGang = Gang::create([
            'name' => 'ShieldedGang',
            'tag' => 'SG',
            'specialty' => 'Schild',
            'shield_until' => now()->addMinutes(12),
        ]);

        $this->actingAs($attackerUser);

        $response = $this->post(route('gangs.attack'), [
            'target_gang_id' => $shieldedGang->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('actief verdedigingsschild', session('error'));
    }

    public function test_gangs_page_renders_balanced_divs_inside_main_container(): void
    {
        $user = User::factory()->create();
        Character::create([
            'user_id' => $user->id,
            'name' => 'LayoutPatient',
            'gender' => 'male',
            'level' => 10,
        ]);

        $this->actingAs($user);

        foreach ([
            route('gangs.index', ['tab' => 'alle_bendes']),
            route('training.index'),
            route('daycare.index'),
            route('droppings.index'),
            route('true_crime.index'),
        ] as $url) {
            $response = $this->get($url);
            $response->assertOk();

            $html = $response->getContent();
            $this->assertNotFalse(preg_match('/<main id="tbs-main-container"[^>]*>(.*?)<\/main>/s', $html, $matches));

            $mainInnerHtml = $matches[1];
            $openDivs = preg_match_all('/<div\b/i', $mainInnerHtml);
            $closeDivs = preg_match_all('/<\/div>/i', $mainInnerHtml);

            $this->assertSame($openDivs, $closeDivs, "Unbalanced divs on {$url}");
        }
    }
}
