<?php

namespace Tests\Feature;

use App\Models\Team;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_exactly_four_teams(): void
    {
        $this->seed(TeamSeeder::class);

        $this->assertSame(4, Team::count());
    }
}
