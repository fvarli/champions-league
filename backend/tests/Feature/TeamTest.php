<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_team_can_be_created(): void
    {
        $team = Team::create([
            'name' => 'Liverpool',
            'strength' => 90,
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Liverpool',
            'strength' => 90,
        ]);
    }

    public function test_strength_is_persisted_as_an_integer(): void
    {
        $team = Team::factory()->create(['strength' => 84]);

        $this->assertSame(84, $team->fresh()->strength);
    }
}
