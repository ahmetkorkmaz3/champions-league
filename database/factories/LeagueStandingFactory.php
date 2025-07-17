<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeagueStanding>
 */
class LeagueStandingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'position' => fake()->numberBetween(1, 20),
            'points' => fake()->numberBetween(0, 60),
            'goals_for' => fake()->numberBetween(0, 50),
            'goals_against' => fake()->numberBetween(0, 50),
            'goal_difference' => fake()->numberBetween(-20, 30),
            'wins' => fake()->numberBetween(0, 20),
            'draws' => fake()->numberBetween(0, 10),
            'losses' => fake()->numberBetween(0, 20),
        ];
    }

    /**
     * Indicate that the team has no points.
     */
    public function zeroPoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
        ]);
    }
}
