<?php

namespace Tests\Feature\Api;

use App\Models\Fixture;
use App\Services\FixtureGenerationService;
use App\Services\MatchSimulationService;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

class LeagueApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TeamSeeder::class);

        // Deterministic match results across the API tests.
        $this->app->instance(
            MatchSimulationService::class,
            new MatchSimulationService(new Randomizer(new Mt19937(2024))),
        );
    }

    private function generateFixtures(): void
    {
        app(FixtureGenerationService::class)->generate();
    }

    public function test_get_teams_returns_the_seeded_teams(): void
    {
        $this->getJson('/api/v1/teams')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'strength']]])
            ->assertJsonPath('data.0.name', 'Liverpool');
    }

    public function test_get_fixtures_returns_fixtures_grouped_by_week(): void
    {
        $this->generateFixtures();

        $response = $this->getJson('/api/v1/fixtures')->assertOk();

        $this->assertCount(6, $response->json('data'));
        $this->assertCount(2, $response->json('data.1'));
        $this->assertCount(2, $response->json('data.6'));
        $response->assertJsonStructure([
            'data' => ['1' => [['id', 'week', 'home_team', 'away_team', 'home_score', 'away_score', 'played_at', 'is_played']]],
        ]);
    }

    public function test_generate_creates_twelve_fixtures(): void
    {
        $this->postJson('/api/v1/fixtures/generate')
            ->assertCreated()
            ->assertJsonCount(12, 'data');

        $this->assertDatabaseCount('matches', 12);
    }

    public function test_generate_fails_clearly_when_fixtures_already_exist(): void
    {
        $this->generateFixtures();

        $this->postJson('/api/v1/fixtures/generate')
            ->assertStatus(409)
            ->assertJsonStructure(['message']);
    }

    public function test_get_standings_returns_all_teams_with_calculated_values(): void
    {
        $this->generateFixtures();

        $this->getJson('/api/v1/standings')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'team' => ['id', 'name', 'strength'],
                    'played', 'won', 'drawn', 'lost',
                    'goals_for', 'goals_against', 'goal_difference', 'points',
                ]],
            ]);
    }

    public function test_play_a_specific_week(): void
    {
        $this->generateFixtures();

        $this->postJson('/api/v1/weeks/1/play')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.is_played', true)
            ->assertJsonPath('data.0.week', 1);

        $this->assertSame(2, Fixture::query()->where('week', 1)->whereNotNull('played_at')->count());
    }

    public function test_play_next_week_plays_the_earliest_unplayed_week(): void
    {
        $this->generateFixtures();

        $this->postJson('/api/v1/weeks/3/play')->assertOk();

        $this->postJson('/api/v1/weeks/next/play')
            ->assertOk()
            ->assertJsonPath('data.0.week', 1)
            ->assertJsonPath('data.1.week', 1);
    }

    public function test_play_all_completes_the_league(): void
    {
        $this->generateFixtures();

        $response = $this->postJson('/api/v1/league/play-all')->assertOk();

        $this->assertCount(6, $response->json('data'));
        $this->assertSame(0, Fixture::query()->whereNull('played_at')->count());
    }

    public function test_predictions_returns_422_before_enough_fixtures_are_played(): void
    {
        $this->generateFixtures();

        $this->getJson('/api/v1/predictions')
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_predictions_returns_percentages_after_eight_fixtures(): void
    {
        $this->generateFixtures();

        for ($week = 1; $week <= 4; $week++) {
            $this->postJson("/api/v1/weeks/{$week}/play")->assertOk();
        }

        $this->getJson('/api/v1/predictions')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure(['data' => [['team' => ['id', 'name'], 'percentage']]]);
    }

    public function test_invalid_week_returns_422(): void
    {
        $this->generateFixtures();

        $this->postJson('/api/v1/weeks/99/play')->assertStatus(422);
    }

    public function test_replaying_a_completed_week_returns_409(): void
    {
        $this->generateFixtures();
        $this->postJson('/api/v1/weeks/1/play')->assertOk();

        $this->postJson('/api/v1/weeks/1/play')->assertStatus(409);
    }

    public function test_playing_all_when_complete_returns_409(): void
    {
        $this->generateFixtures();
        $this->postJson('/api/v1/league/play-all')->assertOk();

        $this->postJson('/api/v1/league/play-all')->assertStatus(409);
    }
}
