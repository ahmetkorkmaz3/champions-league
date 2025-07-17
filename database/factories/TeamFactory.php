<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'power_level' => fake()->numberBetween(70, 95),
            'logo' => fake()->optional()->imageUrl(100, 100, 'sports'),
            'city' => fake()->city(),
        ];
    }
}
