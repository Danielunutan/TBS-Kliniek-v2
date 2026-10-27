<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrisonEscapeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.uniqid(),
            'level' => 5,
            'energy' => 50,
            'max_energy' => 100,
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_reason' => null,
            'prison_escapes' => 0,
            'respect' => 10,
        ], $attrs));
    }

    public function test_cannot_escape_when_not_in_prison(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => false,
            'prison_until' => null,
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('info', 'Je zit momenteel niet in de isoleercel!');
    }

    public function test_escape_fails_when_not_enough_energy(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'energy' => 5,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error');
    }

    public function test_escape_attempt_processes_when_in_prison_with_energy(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $character->refresh();
        $this->assertLessThan(50, $character->energy);

        if ($character->isImprisoned()) {
            $response->assertRedirect(route('prison'));
            $response->assertSessionHas('error');
        } else {
            $response->assertRedirect(route('dashboard'));
            $response->assertSessionHas('success');
            $this->assertFalse($character->is_in_prison);
            $this->assertNull($character->prison_until);
        }
    }

    public function test_escape_attempt_is_limited_to_once_per_sentence(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_escape_attempted' => true,
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error', 'Je hebt je ontsnappingspoging voor deze straftijd al verbruikt! Bekijk een advertentie om direct vrijgelaten te worden.');
        $this->assertEquals(50, $character->fresh()->energy);
    }
}
