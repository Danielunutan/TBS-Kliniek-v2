<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\EscapeTunnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscapeTunnelResetAndHeistTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.uniqid(),
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 10,
            'respect' => 50,
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_escapes' => 0,
        ], $attrs));
    }

    public function test_tunnel_index_renders_for_inmate(): void
    {
        $character = $this->createCharacter();

        $response = $this->actingAs($character->user)->get(route('tunnel.index'));

        $response->assertOk();
        $response->assertSee('De Geheime Uitbraaktunnel');
        $response->assertSee('Graven met Eetlepel');
    }

    public function test_escape_sets_fugitive_and_increments_escapes(): void
    {
        $character = $this->createCharacter();
        $tunnel = EscapeTunnel::create([
            'character_id' => $character->id,
            'stage' => 5,
            'progress_pct' => 100,
            'sand_in_socks' => 0,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($character->user)->post(route('tunnel.escape'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tunnel->refresh();
        $character->refresh();

        $this->assertTrue($tunnel->is_completed);
        $this->assertTrue($tunnel->isFugitive());
        $this->assertEquals(1, $tunnel->times_escaped);
        $this->assertEquals(1, $character->prison_escapes);
        $this->assertEquals(35, $character->stars);
    }

    public function test_fugitive_inmate_can_perform_heist_and_gains_rewards(): void
    {
        $character = $this->createCharacter(['energy' => 50]);
        EscapeTunnel::create([
            'character_id' => $character->id,
            'stage' => 5,
            'progress_pct' => 100,
            'is_completed' => true,
            'fugitive_until' => now()->addHours(2),
            'times_escaped' => 1,
        ]);

        $response = $this->actingAs($character->user)->post(route('tunnel.heist'), [
            'heist_id' => 'safehouse',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(35, $character->energy);
        $this->assertGreaterThan(1000, $character->cash);
        $this->assertGreaterThan(50, $character->respect);
    }

    public function test_heist_fails_when_not_fugitive(): void
    {
        $character = $this->createCharacter();
        EscapeTunnel::create([
            'character_id' => $character->id,
            'is_completed' => false,
            'fugitive_until' => null,
        ]);

        $response = $this->actingAs($character->user)->post(route('tunnel.heist'), [
            'heist_id' => 'safehouse',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Je bent momenteel niet voortvluchtig! Voltooi eerst een uitbraak via de tunnel om buiten te komen.');
    }

    public function test_reset_tunnel_fails_when_not_completed(): void
    {
        $character = $this->createCharacter();
        EscapeTunnel::create([
            'character_id' => $character->id,
            'stage' => 3,
            'progress_pct' => 50,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($character->user)->post(route('tunnel.reset'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_reset_tunnel_succeeds_when_completed(): void
    {
        $character = $this->createCharacter();
        $tunnel = EscapeTunnel::create([
            'character_id' => $character->id,
            'stage' => 5,
            'progress_pct' => 100,
            'sand_in_socks' => 45,
            'is_completed' => true,
            'fugitive_until' => now()->subHour(),
            'times_escaped' => 2,
        ]);

        $response = $this->actingAs($character->user)->post(route('tunnel.reset'));

        $response->assertRedirect(route('tunnel.index'));
        $response->assertSessionHas('success');

        $tunnel->refresh();
        $this->assertFalse($tunnel->is_completed);
        $this->assertEquals(1, $tunnel->stage);
        $this->assertEquals(0, $tunnel->progress_pct);
        $this->assertEquals(0, $tunnel->sand_in_socks);
        $this->assertNull($tunnel->fugitive_until);
        $this->assertEquals(2, $tunnel->times_escaped);
    }

    public function test_tunnel_index_shows_reset_button_when_completed_and_not_fugitive(): void
    {
        $character = $this->createCharacter();
        EscapeTunnel::create([
            'character_id' => $character->id,
            'stage' => 5,
            'progress_pct' => 100,
            'is_completed' => true,
            'fugitive_until' => null,
            'times_escaped' => 1,
        ]);

        $response = $this->actingAs($character->user)->get(route('tunnel.index'));

        $response->assertOk();
        $response->assertSee('Start Nieuwe Uitbraaktunnel (Reset)');
        $response->assertSee('Je Hebt de Vrijheid Bereikt!');
    }

    public function test_tunnel_index_shows_fugitive_heist_hub_when_fugitive(): void
    {
        $character = $this->createCharacter();
        EscapeTunnel::create([
            'character_id' => $character->id,
            'stage' => 5,
            'progress_pct' => 100,
            'is_completed' => true,
            'fugitive_until' => now()->addHours(3),
            'times_escaped' => 1,
        ]);

        $response = $this->actingAs($character->user)->get(route('tunnel.index'));

        $response->assertOk();
        $response->assertSee('Voortvluchtig Buiten de Muren');
        $response->assertSee('Onderduikers Safehouse Kraak');
        $response->assertSee('Kliniekdirecteur Residentie Inbraak');
        $response->assertSee('Geldtransport Onderschepping');
    }
}
