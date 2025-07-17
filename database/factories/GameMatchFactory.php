<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameMatch>
 */
class GameMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'home_score' => fake()->optional()->numberBetween(0, 5),
            'away_score' => fake()->optional()->numberBetween(0, 5),
            'week' => fake()->numberBetween(1, 6),
            'is_played' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the match is played.
     */
    public function played(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_played' => true,
            'home_score' => fake()->numberBetween(0, 5),
            'away_score' => fake()->numberBetween(0, 5),
        ]);
    }

    /**
     * Indicate that the match is not played.
     */
    public function unplayed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_played' => false,
            'home_score' => null,
            'away_score' => null,
        ]);
    }
}
