<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Seed the league's four teams with their relative strengths.
     */
    public function run(): void
    {
        $teams = [
            ['name' => 'Liverpool', 'strength' => 90],
            ['name' => 'Manchester City', 'strength' => 88],
            ['name' => 'Chelsea', 'strength' => 82],
            ['name' => 'Arsenal', 'strength' => 80],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
