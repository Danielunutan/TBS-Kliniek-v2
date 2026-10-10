<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Friend;
use App\Models\Gang;
use App\Models\TimelinePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAndGangUpgradesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_friends_system_add_bff_and_respect(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $charA = $user->character;

        $otherUser = User::factory()->create();
        $charB = Character::create([
            'user_id' => $otherUser->id,
            'name' => 'TestPatientB',
            'gender' => 'male',
            'level' => 5,
            'cash' => 1000,
            'respect' => 10,
        ]);

        $this->actingAs($user);

        // 1. Visit Friends page
        $res = $this->get(route('friends.index'));
        $res->assertOk();

        // 2. Add as friend
        $addRes = $this->post(route('friends.add'), [
            'friend_name' => 'TestPatientB',
        ]);
        $addRes->assertSessionHas('success');

        $this->assertDatabaseHas('friends', [
            'character_id' => $charA->id,
            'friend_id' => $charB->id,
        ]);

        $friendRecord = Friend::where('character_id', $charA->id)->where('friend_id', $charB->id)->first();

        // 3. Toggle BFF
        $bffRes = $this->post(route('friends.toggle-bff', $friendRecord));
        $bffRes->assertSessionHas('success');
        $friendRecord->refresh();
        $this->assertTrue($friendRecord->is_bff);

        // 4. Give Respect with Captcha Check
        session(['friend_captcha_ans' => 15]);

        // Wrong captcha fails
        $wrongRes = $this->post(route('friends.respect', $friendRecord), [
            'captcha_answer' => 9,
        ]);
        $wrongRes->assertSessionHas('error');

        // Correct captcha succeeds with BFF double respect bonus (+2)
        $initialRespect = $charB->respect;
        $correctRes = $this->post(route('friends.respect', $friendRecord), [
            'captcha_answer' => 15,
        ]);
        $correctRes->assertSessionHas('success');

        $charB->refresh();
        $this->assertEquals($initialRespect + 2, $charB->respect);

        // 5. Mass Message to Friends
        $massRes = $this->post(route('friends.mass-message'), [
            'subject' => 'Bijeenkomst om 20:00',
            'message' => 'Iedereen verzamelen in de recreatiezaal voor de bende-instructie.',
        ]);
        $massRes->assertSessionHas('success');

        // Cooldown prevents immediate second mass message
        $massRes2 = $this->post(route('friends.mass-message'), [
            'subject' => 'Nog een bericht',
            'message' => 'Test cooldown.',
        ]);
        $massRes2->assertSessionHas('error');
    }

    public function test_timeline_public_feed(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $this->actingAs($user);

        // 1. Visit Timeline
        $res = $this->get(route('timeline.index'));
        $res->assertOk();

        // 2. Create post
        $postRes = $this->post(route('timeline.store'), [
            'content' => 'Rookruimte is geopend vandaag! Wie heeft er aanstekers?',
        ]);
        $postRes->assertSessionHas('success');

        $post = TimelinePost::where('character_id', $character->id)->first();
        $this->assertNotNull($post);
        $this->assertEquals('Rookruimte is geopend vandaag! Wie heeft er aanstekers?', $post->content);

        // 3. Like post
        $likeRes = $this->post(route('timeline.like', $post));
        $likeRes->assertSessionHas('success');
        $post->refresh();
        $this->assertEquals(1, $post->likes_count);

        // 4. Delete post
        $delRes = $this->delete(route('timeline.destroy', $post));
        $delRes->assertSessionHas('success');
        $this->assertDatabaseMissing('timeline_posts', ['id' => $post->id]);
    }

    public function test_gang_shop_upgrades_and_star_donations(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'WinkelGang',
            'tag' => 'WG',
            'specialty' => 'Handel',
            'leader_character_id' => $character->id,
            'treasury' => 2000000,
            'max_members' => 10,
            'protection_points' => 1000,
            'attack_power' => 500,
            'bling_shops' => 0,
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
            'cash' => 50000,
            'stars' => 1500,
        ]);

        $this->actingAs($user);

        // 1. Buy Bling Bling Shop (costs €1.250.000 from gang treasury)
        $shopRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'bling_shops',
            'quantity' => 1,
        ]);
        $shopRes->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals(1, $gang->bling_shops);
        $this->assertEquals(750000, $gang->treasury);

        // 2. Buy Protection (costs €15.000 from gang treasury)
        $protRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'protection',
            'quantity' => 2,
        ]);
        $protRes->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals(11000, $gang->protection_points); // 1000 + (2 * 5000)

        // 3. Buy member slots (costs 500 stars per slot)
        $slotRes = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'max_members',
            'quantity' => 2,
        ]);
        $slotRes->assertSessionHas('success');
        $gang->refresh();
        $character->refresh();
        $this->assertEquals(12, $gang->max_members);
        $this->assertEquals(500, $character->stars); // 1500 - 1000

        // 4. Donate stars to gang
        $starRes = $this->post(route('gangs.donate-stars'), [
            'stars' => 250,
        ]);
        $starRes->assertSessionHas('success');
        $gang->refresh();
        $character->refresh();
        $this->assertEquals(250, $gang->gang_stars);
        $this->assertEquals(250, $character->stars);
    }
}
