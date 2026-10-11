<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GuestbookEntry;
use App\Models\TimelinePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationAndRoleRankingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacterWithUser(string $name, string $role = 'user', bool $isStaff = false): Character
    {
        $user = User::factory()->create([
            'role' => $role,
            'is_staff' => $isStaff,
        ]);

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 1000,
            'bank' => 500,
            'stars' => 10,
            'respect' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
        ]);
    }

    public function test_moderator_can_delete_other_user_timeline_post(): void
    {
        $regularChar = $this->createCharacterWithUser('PaterRegular', 'user');

        $post = TimelinePost::create([
            'character_id' => $regularChar->id,
            'content' => 'Ongepast bericht dat verwijderd moet worden',
            'likes_count' => 0,
        ]);

        $modChar = $this->createCharacterWithUser('ModMaster', 'moderator', true);

        $this->actingAs($modChar->user);
        $response = $this->delete(route('timeline.destroy', $post));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('timeline_posts', ['id' => $post->id]);
    }

    public function test_regular_user_cannot_delete_other_user_timeline_post(): void
    {
        $authorChar = $this->createCharacterWithUser('AuteurPatiënt', 'user');

        $post = TimelinePost::create([
            'character_id' => $authorChar->id,
            'content' => 'Mijn normale bericht',
            'likes_count' => 0,
        ]);

        $otherChar = $this->createCharacterWithUser('AnderLid', 'user');

        $this->actingAs($otherChar->user);
        $response = $this->delete(route('timeline.destroy', $post));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('timeline_posts', ['id' => $post->id]);
    }

    public function test_moderator_can_delete_guestbook_entry(): void
    {
        $authorChar = $this->createCharacterWithUser('SchrijverLid', 'user');
        $targetChar = $this->createCharacterWithUser('OntvangerLid', 'user');

        $entry = GuestbookEntry::create([
            'profile_character_id' => $targetChar->id,
            'author_character_id' => $authorChar->id,
            'message' => 'Nare reactie in gastenboek',
        ]);

        $modChar = $this->createCharacterWithUser('ModKliniek', 'moderator', true);

        $this->actingAs($modChar->user);
        $response = $this->delete(route('guestbook.destroy', $entry));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('guestbook_entries', ['id' => $entry->id]);
    }

    public function test_admin_can_rank_player_to_moderator_in_admin_panel(): void
    {
        $adminChar = $this->createCharacterWithUser('AdminBaas', 'admin', true);
        $targetChar = $this->createCharacterWithUser('NieuweModKandidaat', 'user', false);

        $this->actingAs($adminChar->user);
        $response = $this->post(route('admin.player.update', $targetChar), [
            'cash' => $targetChar->cash,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'moderator',
        ]);

        $response->assertSessionHas('success');
        $targetUser = $targetChar->user->fresh();
        $this->assertEquals('moderator', $targetUser->role);
        $this->assertTrue($targetUser->is_staff);
        $this->assertTrue($targetUser->isModerator());
        $this->assertTrue($targetUser->canModerate());
    }

    public function test_non_owner_cannot_assign_owner_role(): void
    {
        $adminChar = $this->createCharacterWithUser('AdminOnly', 'admin', true);
        $targetChar = $this->createCharacterWithUser('TestSpeler', 'user', false);

        $this->actingAs($adminChar->user);
        $response = $this->post(route('admin.player.update', $targetChar), [
            'cash' => $targetChar->cash,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'owner',
        ]);

        $response->assertSessionHas('error');
        $targetUser = $targetChar->user->fresh();
        $this->assertNotEquals('owner', $targetUser->role);
    }

    public function test_owner_can_assign_owner_role(): void
    {
        $ownerChar = $this->createCharacterWithUser('OwnerChief', 'owner', true);
        $targetChar = $this->createCharacterWithUser('TweedeEigenaar', 'user', false);

        $this->actingAs($ownerChar->user);
        $response = $this->post(route('admin.player.update', $targetChar), [
            'cash' => $targetChar->cash,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'owner',
        ]);

        $response->assertSessionHas('success');
        $targetUser = $targetChar->user->fresh();
        $this->assertEquals('owner', $targetUser->role);
        $this->assertTrue($targetUser->isOwner());
    }
}
