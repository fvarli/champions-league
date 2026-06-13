<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_match_belongs_to_its_home_team(): void
    {
        $home = Team::factory()->create();
        $match = Fixture::factory()->create(['home_team_id' => $home->id]);

        $this->assertTrue($match->homeTeam->is($home));
    }

    public function test_a_match_belongs_to_its_away_team(): void
    {
        $away = Team::factory()->create();
        $match = Fixture::factory()->create(['away_team_id' => $away->id]);

        $this->assertTrue($match->awayTeam->is($away));
    }
}
