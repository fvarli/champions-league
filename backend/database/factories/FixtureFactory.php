<?php

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
class FixtureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Matches are created unplayed: scores and the kickoff time stay null
     * until the simulation engine resolves them.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'week' => fake()->numberBetween(1, 6),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'home_score' => null,
            'away_score' => null,
            'played_at' => null,
        ];
    }
}
