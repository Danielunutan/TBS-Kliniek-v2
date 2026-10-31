<?php

namespace Database\Factories;

use App\Models\Gang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gang>
 */
class GangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company,
            'tag' => strtoupper($this->faker->unique()->lexify('???')),
            'treasury' => 1000000,
            'specialty' => 'Agressie',
            'emblem' => 'fa-crown',
            'leader_character_id' => \App\Models\Character::factory(),
        ];
    }
}
