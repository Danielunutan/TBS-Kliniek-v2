<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterCooldown;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\RadioShoutout;
use App\Models\SocialRewardClaim;
use App\Models\TimelinePost;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket2AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_rejects_html_only_posts_and_comments_after_strip_tags(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'TijdlijnTester',
        ]);

        $response = $this->actingAs($user)->post(route('timeline.store'), [
            'content' => '<b></b><i> </i>',
        ]);
        $response->assertSessionHas('error', 'Voer een geldig bericht van minimaal 2 tekens in.');
        $this->assertSame(0, TimelinePost::count());

        $post = TimelinePost::create([
            'character_id' => $character->id,
            'content' => 'Echt bericht op de tijdlijn',
            'likes_count' => 0,
        ]);

        $commentResponse = $this->actingAs($user)->post(route('timeline.comment', $post), [
            'content' => '<b> </b>',
        ]);
        $commentResponse->assertSessionHas('error', 'Voer een geldige reactie in.');
        $this->assertSame(0, $post->comments()->count());
    }

    public function test_timeline_like_toggles_and_updates_likes_count_atomically(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'LikerPatient',
        ]);

        $post = TimelinePost::create([
            'character_id' => $character->id,
            'content' => 'Status bericht',
            'likes_count' => 0,
        ]);

        $this->actingAs($user)->postJson(route('timeline.like', $post), ['emoji' => '🔥'])
            ->assertOk()
            ->assertJsonPath('action', 'liked')
            ->assertJsonPath('likes_count', 1);

        $this->actingAs($user)->postJson(route('timeline.like', $post), ['emoji' => '🔥'])
            ->assertOk()
            ->assertJsonPath('action', 'unliked')
            ->assertJsonPath('likes_count', 0);
    }

    public function test_travel_controller_blocks_comatose_character_from_moving(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'current_location' => 'paviljoen_a',
            'cash' => 1000,
            'xp' => 0,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $response = $this->actingAs($user)->post(route('travel.move'), [
            'location' => 'sportzaal',
        ]);

        $response->assertSessionHas('error');
        $character->refresh();
        $this->assertSame('paviljoen_a', $character->current_location);
        $this->assertSame(0, $character->xp);
    }

    public function test_character_remaining_seconds_helpers_use_ceil_and_preserve_prison_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00.400'));

        try {
            $until = Carbon::parse('2026-10-31 10:00:01');
            $user = User::factory()->create(['is_onboarded' => true]);
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'is_in_prison' => true,
                'prison_until' => $until,
                'leave_until' => $until,
                'sedated_until' => $until,
                'protection_until' => $until,
                'cash_protection_until' => $until,
                'vest_until' => $until,
                'health' => 0,
                'coma_until' => $until,
            ]);

            $this->assertSame(1, $character->remainingPrisonSeconds());
            $this->assertTrue((bool) $character->fresh()->is_in_prison);
            $character->is_in_prison = false;
            $character->prison_until = null;
            $this->assertSame(1, $character->getLeaveRemainingSeconds());
            $this->assertSame(1, $character->getSedatedRemainingSeconds());
            $this->assertSame(1, $character->getIsolationProtectionRemainingSeconds());
            $this->assertSame(1, $character->getCashProtectionRemainingSeconds());
            $this->assertSame(1, $character->getComaRemainingSeconds());
            $this->assertSame(1, $character->getVestRemainingSeconds());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_character_cooldown_remaining_uses_ceil_rounding(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00.400'));

        try {
            $user = User::factory()->create(['is_onboarded' => true]);
            $character = Character::factory()->create([
                'user_id' => $user->id,
            ]);

            CharacterCooldown::create([
                'character_id' => $character->id,
                'action' => 'heist',
                'cooldown_until' => Carbon::parse('2026-10-31 10:00:01.000'),
            ]);

            $this->assertSame(1, $character->cooldownRemaining('heist'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_theft_controller_blocks_imprisoned_character(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($user)->post(route('theft.steal'), [
            'tier' => 'klein',
        ]);

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error', 'Vanuit de isoleercel kun je niet zakkenrollen!');
    }

    public function test_garage_buy_blocks_unavailable_vehicle(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 50000,
            'level' => 5,
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Speciaal Prototype',
            'slug' => 'speciaal-prototype',
            'type' => 'auto',
            'price_cash' => 10000,
            'price_stars' => 0,
            'speed_pct' => 60,
            'reliability_pct' => 80,
            'min_level' => 1,
            'is_available' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.buy', $vehicle));
        $response->assertSessionHas('error', 'De Speciaal Prototype is momenteel niet beschikbaar in de showroom!');
        $this->assertSame(0, $character->vehicles()->count());
    }

    public function test_gang_property_assign_npc_blocks_imprisoned_character(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(20),
        ]);
        $gang = Gang::factory()->create(['leader_character_id' => $character->id]);
        $character->update(['gang_id' => $gang->id]);

        $property = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'wietkwekerij',
            'name' => 'Wietkwekerij Loods',
            'type' => 'production',
            'purchase_price' => 50000,
        ]);

        $response = $this->actingAs($user)->post(route('gangs.panden.assign-npc', $property), [
            'npc_id' => null,
        ]);

        $response->assertSessionHas('error', 'Vanuit de isoleercel kun je geen NPC bendeleden beheren!');
    }

    public function test_news_comment_rejects_tag_only_empty_content(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
        ]);

        $article = NewsArticle::create([
            'character_id' => $character->id,
            'title' => 'Kliniek Nieuws',
            'slug' => 'kliniek-nieuws',
            'content' => 'Inhoud van het bericht',
            'is_published' => true,
            'comments_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('news.comment', $article), [
            'content' => '<b> </b>',
        ]);

        $response->assertSessionHas('error', 'Voer een geldige reactie van minimaal 2 tekens in.');
        $this->assertSame(0, NewsComment::count());
    }

    public function test_radio_shoutout_rejects_empty_stripped_message_without_cash_deduction(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 500,
        ]);

        $response = $this->actingAs($user)->post(route('radio.shoutout'), [
            'message' => '<b> </b>',
            'track_title' => 'Test Track',
        ]);

        $response->assertSessionHas('error', 'Shoutout bericht moet minimaal 3 tekens bevatten.');
        $character->refresh();
        $this->assertSame(500, $character->cash);
        $this->assertSame(0, RadioShoutout::count());
    }

    public function test_hitman_train_cold_blooded_consumes_energy_via_energy_service(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 1000000,
            'bank' => 0,
            'energy' => 5, // Requires 15 energy
            'max_energy' => 100,
            'last_energy_refill_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('hitman.train_cold_blooded'), [
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('error', 'Je hebt minimaal 15 energie nodig voor een intensieve schietbaansessie!');
        $character->refresh();
        $this->assertSame(1000000, $character->cash);
    }

    public function test_tunnel_dig_consumes_energy_via_energy_service(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'energy' => 5, // Requires 20 energy
            'max_energy' => 100,
            'last_energy_refill_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('tunnel.dig'));
        $response->assertSessionHas('error', 'Je bent bekaf. Je hebt minimaal 20 energie nodig om te graven!');
    }

    public function test_push_claim_persists_cash_and_energy_rewards(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 100,
            'energy' => 10,
            'max_energy' => 100,
            'respect' => 5,
        ]);

        $response = $this->actingAs($user)->postJson(route('push.subscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-12345',
        ]);

        $response->assertOk();
        $character->refresh();
        $this->assertSame(25100, $character->cash);
        $this->assertSame(30, $character->energy);
        $this->assertSame(30, $character->respect);
        $this->assertSame(1, SocialRewardClaim::where('user_id', $user->id)->where('platform', 'push')->count());
    }
}
