<?php

namespace Tests\Feature;

use App\Http\Controllers\CommunityController;
use App\Models\Bounty;
use App\Models\Character;
use App\Models\CinemaRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket8AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
            'is_onboarded' => true,
        ]);

        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'InmateAuditor',
            'cash' => 50000,
            'bank' => 100000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 10,
            'stars' => 25,
            'prison_until' => null,
            'coma_until' => null,
        ]);
    }

    public function test_chat_view_renders_with_auth_char_avatar_and_guards(): void
    {
        $response = $this->actingAs($this->user)->get(route('chat.index'));
        $response->assertStatus(200);
        $response->assertSee('authCharAvatar', false);
        $response->assertSee('myName: config.authCharName', false);
        $response->assertSee('myAvatar: config.authCharAvatar', false);

        // Imprison character and verify chat notice
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(30), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('chat.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('Je zit in de isoleercel. Je kunt pas weer berichten sturen zodra je vrijkomt.');

        // Comatose character and verify code blauw notice
        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => Carbon::now()->addMinutes(30),
        ]);
        $this->user->refresh();
        $responseComa = $this->actingAs($this->user)->get(route('chat.index'));
        $responseComa->assertStatus(200);
        $responseComa->assertSee('Je verkeert in coma (Code Blauw).');
    }

    public function test_battle_view_guards_milestone_and_focus_when_comatose_or_imprisoned(): void
    {
        $responseNormal = $this->actingAs($this->user)->get(route('battle.index'));
        $responseNormal->assertStatus(200);
        $responseNormal->assertSee('Selecteer jouw Focus-Sector');

        // Imprisoned state
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(20), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('battle.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('Je kunt momenteel geen focus-sector vergrendelen omdat je in de isoleercel of in coma verkeert.');
    }

    public function test_battlepass_view_includes_guards_for_comatose_and_imprisoned(): void
    {
        $responseNormal = $this->actingAs($this->user)->get(route('battlepass.index'));
        $responseNormal->assertStatus(200);
        $responseNormal->assertSee('isImprisoned', false);
        $responseNormal->assertSee('isComatose', false);

        // Imprisoned state
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(20), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('battlepass.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('isImprisoned', false);
        $responseJail->assertSee('In Isoleercel');
    }

    public function test_bounties_view_renders_guards_when_comatose_or_imprisoned(): void
    {
        $targetUser = User::factory()->create(['is_onboarded' => true]);
        $targetChar = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'TargetInmate',
            'level' => 5,
        ]);

        Bounty::create([
            'target_character_id' => $targetChar->id,
            'placed_by_character_id' => $this->character->id,
            'amount' => 5000,
            'reason' => 'Verraad',
            'is_claimed' => false,
        ]);

        $responseNormal = $this->actingAs($this->user)->get(route('bounties.index'));
        $responseNormal->assertStatus(200);
        $responseNormal->assertSee('Vecht & Incasseer', false);

        // Imprisoned state
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(15), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('bounties.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('cursor-not-allowed', false);
        $responseJail->assertSee('Je kunt momenteel geen kopgeld uitzetten');
    }

    public function test_cinema_index_and_room_views_render_with_proper_avatar_and_guards(): void
    {
        $room = CinemaRoom::create([
            'title' => 'Audit Zaal 1',
            'slug' => 'audit-zaal-1',
            'description' => 'Test zaal voor filmavond',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'host_character_id' => $this->character->id,
            'is_official' => true,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('cinema.index'));
        $response->assertStatus(200);
        $response->assertSee('Audit Zaal 1');

        $responseRoom = $this->actingAs($this->user)->get(route('cinema.show', $room->slug));
        $responseRoom->assertStatus(200);
        $responseRoom->assertSee($this->character->avatar_url, false);
    }

    public function test_community_view_displays_dynamic_energy_and_guards_claim(): void
    {
        $platforms = CommunityController::platforms();
        $expectedTotalEnergy = array_sum(array_column(array_column($platforms, 'reward'), 'energy'));

        $response = $this->actingAs($this->user)->get(route('community.index'));
        $response->assertStatus(200);
        $response->assertSee("+{$expectedTotalEnergy}");
        $response->assertSee('alle '.count($platforms).' platforms');

        // When imprisoned, claim buttons are disabled with warning
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(25), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('community.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('In Isoleercel');
    }

    public function test_connectfour_view_includes_comatose_and_imprisoned_guards(): void
    {
        $responseNormal = $this->actingAs($this->user)->get(route('connect_four.index'));
        $responseNormal->assertStatus(200);
        $responseNormal->assertSee('data-is-blocked="0"', false);

        // When imprisoned
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(15), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('connect_four.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('data-is-blocked="1"', false);
        $responseJail->assertSee('Je zit in de isoleercel!');
    }

    public function test_daily_rewards_view_guards_wheel_and_streak_claims(): void
    {
        $responseNormal = $this->actingAs($this->user)->get(route('daily.index'));
        $responseNormal->assertStatus(200);
        $responseNormal->assertSee('Gratis Draaien!');

        // When comatose
        $this->character->update(['coma_until' => Carbon::now()->addMinutes(30)]);
        $this->user->refresh();
        $responseComa = $this->actingAs($this->user)->get(route('daily.index'));
        $responseComa->assertStatus(200);
        $responseComa->assertSee('In Coma (Niet Beschikbaar)');
    }

    public function test_casino_view_renders_with_imprisoned_and_comatose_alert_banners(): void
    {
        $this->character->update(['prison_until' => Carbon::now()->addMinutes(20), 'is_in_prison' => true]);
        $this->user->refresh();
        $responseJail = $this->actingAs($this->user)->get(route('casino.index'));
        $responseJail->assertStatus(200);
        $responseJail->assertSee('Je zit in de isoleercel. Je kunt pas weer meespelen in het casino zodra je vrijkomt.');

        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => Carbon::now()->addMinutes(20),
        ]);
        $this->user->refresh();
        $responseComa = $this->actingAs($this->user)->get(route('casino.index'));
        $responseComa->assertStatus(200);
        $responseComa->assertSee('Je verkeert in coma (Code Blauw).');
    }

    public function test_combat_training_view_renders_properly(): void
    {
        $response = $this->actingAs($this->user)->get(route('combat_training.index'));
        $response->assertStatus(200);
        $response->assertSee('Vechtoefening &amp; Training', false);
        $response->assertSee('Kracht (Lvl 10 Max)');
    }
}
