<?php

namespace Tests\Feature;

use App\Exceptions\FixtureAlreadyPlayedException;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

class MatchSimulationServiceTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_GOALS = 5;

    /**
     * Build a service with a seeded engine so results are reproducible.
     */
    private function service(int $seed = 1): MatchSimulationService
    {
        return new MatchSimulationService(new Randomizer(new Mt19937($seed)));
    }

    private function unplayedFixture(int $homeStrength = 80, int $awayStrength = 80): Fixture
    {
        $home = Team::factory()->create(['strength' => $homeStrength]);
        $away = Team::factory()->create(['strength' => $awayStrength]);

        return Fixture::factory()->create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);
    }

    public function test_simulating_sets_scores_and_played_at(): void
    {
        $result = $this->service()->simulate($this->unplayedFixture());

        $this->assertNotNull($result->home_score);
        $this->assertNotNull($result->away_score);
        $this->assertNotNull($result->played_at);
    }

    public function test_scores_are_non_negative_integers(): void
    {
        $result = $this->service()->simulate($this->unplayedFixture());

        $this->assertIsInt($result->home_score);
        $this->assertIsInt($result->away_score);
        $this->assertGreaterThanOrEqual(0, $result->home_score);
        $this->assertGreaterThanOrEqual(0, $result->away_score);
    }

    public function test_scores_stay_within_a_realistic_upper_bound(): void
    {
        $service = $this->service(42);

        for ($i = 0; $i < 50; $i++) {
            $result = $service->simulate($this->unplayedFixture());

            $this->assertGreaterThanOrEqual(0, $result->home_score);
            $this->assertGreaterThanOrEqual(0, $result->away_score);
            $this->assertLessThanOrEqual(self::MAX_GOALS, $result->home_score);
            $this->assertLessThanOrEqual(self::MAX_GOALS, $result->away_score);
        }
    }

    public function test_already_played_fixtures_cannot_be_simulated_again(): void
    {
        $fixture = $this->unplayedFixture();
        $service = $this->service();

        $service->simulate($fixture);

        $this->expectException(FixtureAlreadyPlayedException::class);

        $service->simulate($fixture);
    }

    public function test_replaying_a_fixture_does_not_change_the_stored_result(): void
    {
        $fixture = $this->unplayedFixture();
        $played = $this->service()->simulate($fixture);

        try {
            $this->service(99)->simulate($fixture);
        } catch (FixtureAlreadyPlayedException) {
            // expected
        }

        $stored = $fixture->fresh();
        $this->assertSame($played->home_score, $stored->home_score);
        $this->assertSame($played->away_score, $stored->away_score);
    }

    public function test_stronger_teams_win_more_often(): void
    {
        $service = $this->service(2024);

        $homeWins = 0;
        $awayWins = 0;

        for ($i = 0; $i < 200; $i++) {
            $result = $service->simulate($this->unplayedFixture(homeStrength: 90, awayStrength: 50));

            if ($result->home_score > $result->away_score) {
                $homeWins++;
            } elseif ($result->home_score < $result->away_score) {
                $awayWins++;
            }
        }

        $this->assertGreaterThan($awayWins, $homeWins);
    }

    public function test_a_weaker_team_can_still_win(): void
    {
        $service = $this->service(2024);

        $awayWins = 0;

        for ($i = 0; $i < 200; $i++) {
            $result = $service->simulate($this->unplayedFixture(homeStrength: 85, awayStrength: 70));

            if ($result->away_score > $result->home_score) {
                $awayWins++;
            }
        }

        $this->assertGreaterThan(0, $awayWins);
    }

    public function test_simulation_does_not_change_team_data(): void
    {
        $fixture = $this->unplayedFixture(homeStrength: 88, awayStrength: 77);
        $home = $fixture->homeTeam;
        $away = $fixture->awayTeam;

        $this->service()->simulate($fixture);

        $this->assertSame(88, $home->fresh()->strength);
        $this->assertSame(77, $away->fresh()->strength);
        $this->assertSame($home->name, $home->fresh()->name);
    }

    public function test_the_returned_fixture_is_persisted(): void
    {
        $result = $this->service()->simulate($this->unplayedFixture());

        $this->assertDatabaseHas('matches', [
            'id' => $result->id,
            'home_score' => $result->home_score,
            'away_score' => $result->away_score,
        ]);
        $this->assertNotNull($result->fresh()->played_at);
    }
}
