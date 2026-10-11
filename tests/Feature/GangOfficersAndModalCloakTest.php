<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangOfficersAndModalCloakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(string $name, ?int $gangId = null, string $role = 'member'): Character
    {
        $user = User::factory()->create();

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 1000,
            'bank' => 500,
            'respect' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'level' => 1,
            'xp' => 0,
            'gang_id' => $gangId,
            'gang_role' => $role,
        ]);
    }

    public function test_personal_shopper_and_money_magnet_have_gang_admin_officer_permissions(): void
    {
        $leaderUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $leader = $leaderUser->character;

        $gang = Gang::create([
            'name' => 'Syndicaat Officers',
            'tag' => 'SOF',
            'specialty' => 'Kantine Controle',
            'leader_character_id' => $leader->id,
            'is_open' => true,
        ]);

        $leader->gang_id = $gang->id;
        $leader->gang_role = 'leader';
        $leader->save();

        // Create Kantine Smokkelaar (personal shopper)
        $shopper = $this->createCharacter('KantineSmokkelaar', $gang->id);
        $gang->personal_shopper = $shopper->id;

        // Create Kasbeheerder (money magnet)
        $magnet = $this->createCharacter('KasBeheerder', $gang->id);
        $gang->money_magnet = $magnet->id;
        $gang->save();

        // 1. Verify model helper isOfficer
        $this->assertTrue($gang->isOfficer($leader));
        $this->assertTrue($gang->isOfficer($shopper));
        $this->assertTrue($gang->isOfficer($magnet));

        // Create regular member
        $regular = $this->createCharacter('GewoonLid', $gang->id);
        $this->assertFalse($gang->isOfficer($regular));

        // 2. Kantine Smokkelaar can access beheer view and see management tools
        $this->actingAs($shopper->user);
        $res = $this->get(route('gangs.index', ['tab' => 'beheer']));
        $res->assertStatus(200);
        $res->assertSee('Bende Beheer');
        $res->assertSee('Kantine Smokkelaar');
        $res->assertDontSee('Spionnen'); // Spionnen removed from gang menu

        // 3. Kasbeheerder can handle join request
        $applicant = $this->createCharacter('Sollicitant');

        $joinReq = GangRequest::create([
            'gang_id' => $gang->id,
            'character_id' => $applicant->id,
            'status' => 'pending',
            'message' => 'Ik wil erbij!',
        ]);

        $this->actingAs($magnet->user);
        $acceptRes = $this->post(route('gangs.handle-request', $joinReq), [
            'action' => 'accept',
        ]);
        $acceptRes->assertSessionHas('success');

        $applicant->refresh();
        $this->assertEquals($gang->id, $applicant->gang_id);

        // 4. Kantine Smokkelaar can kick a regular member
        $this->actingAs($shopper->user);
        $kickRes = $this->post(route('gangs.kick', $regular));
        $kickRes->assertSessionHas('success');

        $regular->refresh();
        $this->assertNull($regular->gang_id);

        // 5. Kasbeheerder can update gang settings
        $this->actingAs($magnet->user);
        $settingRes = $this->post(route('gangs.settings'), [
            'action_type' => 'motd',
            'motd' => 'Nieuwe MOTD door Kasbeheerder!',
        ]);
        $settingRes->assertSessionHas('success');

        $gang->refresh();
        $this->assertEquals('Nieuwe MOTD door Kasbeheerder!', $gang->motd);
    }
}
