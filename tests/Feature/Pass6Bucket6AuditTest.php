<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\ControlPoint;
use App\Models\EscapeTunnel;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket6AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_connect_four_view_includes_skip_cooldown_with_stars_action(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('connect_four.index'));
        $response->assertOk();
        $response->assertSee(route('connect_four.skip_cooldown_stars'), false);
    }

    public function test_cinema_index_loads_successfully(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('cinema.index'));
        $response->assertOk();
        $response->assertSee('Kliniek Cinema & Watchparties');
    }

    public function test_combat_training_and_droppings_views_disable_buttons_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'level' => 5,
            'cash' => 10000,
            'energy' => 100,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user)->get(route('combat_training.index'))
            ->assertOk()
            ->assertSee('disabled', false);

        $this->actingAs($user)->get(route('droppings.index'))
            ->assertOk()
            ->assertSee('disabled', false);
    }

    public function test_friends_view_escapes_available_patients_safely_with_js_directive(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $otherUser = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Karel "Het Mes" Bakker',
        ]);

        $response = $this->actingAs($user)->get(route('friends.index'));
        $response->assertOk();
        $response->assertSee('patients: JSON.parse(', false);
        $response->assertDontSee('patients: [{"name":"Karel "Het Mes" Bakker"', false);
    }

    public function test_depot_view_escapes_sell_item_names_safely_with_js_directive(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create(['user_id' => $user->id]);

        $item = Item::create([
            'name' => '9mm "Silencer" Special',
            'slug' => '9mm-silencer-special',
            'description' => 'Geluiddemper',
            'category' => 'weapon',
            'rarity' => 'common',
            'price_kc' => 500,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $item->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('depot.index'));
        $response->assertOk();
        $response->assertSee('sellModalItem = { id:', false);
        $response->assertDontSee("name: '9mm \"Silencer\" Special'", false);
    }

    public function test_faq_view_uses_safe_js_matching_and_handles_search(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('faq.index'));
        $response->assertOk();
        $response->assertSee('matchesSearch(', false);
        $response->assertDontSee('addcslashes', false);
    }

    public function test_dashboard_guards_tunnel_and_packages_when_comatose_or_imprisoned(): void
    {
        $comatoseUser = User::factory()->create(['is_onboarded' => true]);
        $comatoseChar = Character::factory()->create([
            'user_id' => $comatoseUser->id,
            'coma_until' => now()->addHours(2),
        ]);
        EscapeTunnel::create(['character_id' => $comatoseChar->id, 'stage' => 1]);

        $comatoseResponse = $this->actingAs($comatoseUser)->get(route('dashboard'));
        $comatoseResponse->assertOk();
        $comatoseResponse->assertSee('In coma &bull; Graven geblokkeerd', false);
        $comatoseResponse->assertSee('In coma &bull; Postkamer geblokkeerd', false);

        $jailedUser = User::factory()->create(['is_onboarded' => true]);
        $jailedChar = Character::factory()->create([
            'user_id' => $jailedUser->id,
            'is_in_prison' => true,
            'prison_until' => now()->addHours(2),
        ]);
        EscapeTunnel::create(['character_id' => $jailedChar->id, 'stage' => 1]);

        $jailedResponse = $this->actingAs($jailedUser)->get(route('dashboard'));
        $jailedResponse->assertOk();
        $jailedResponse->assertSee('In isoleercel &bull; Graven geblokkeerd', false);
        $jailedResponse->assertSee('In isoleercel &bull; Postkamer geblokkeerd', false);
    }

    public function test_control_point_banner_renders_safely_for_guest_or_empty_character(): void
    {
        ControlPoint::firstOrCreate(
            ['key' => 'luchtplaats'],
            [
                'name' => 'Luchtplaats Bankje',
                'location_name' => 'De Luchtplaats',
                'page_route' => 'fight.index',
                'description' => 'Test',
                'energy_cost' => 10,
            ]
        );

        $rendered = view('game.control_points.banner', ['slug' => 'luchtplaats', 'cpCharacter' => null])->render();
        $this->assertStringContainsString('Geen Karakter', $rendered);
        $this->assertStringNotContainsString('route(\'control-points.capture\'', $rendered);
    }

    public function test_fight_view_shows_comatose_guard_banner(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'coma_until' => now()->addHours(2),
        ]);

        $response = $this->actingAs($user)->get(route('fight.index'));
        $response->assertOk();
        $response->assertSee('Je ligt momenteel in coma in de ziekenboeg!', false);
    }

    public function test_daily_rewards_view_renders_correctly(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('daily.index'));
        $response->assertOk();
        $response->assertSee('tbsDailyRewards', false);
    }
}
