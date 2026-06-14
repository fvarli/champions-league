<?php

namespace Tests\Feature\Api;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\FixtureGenerationService;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoResetTest extends TestCase
{
    use RefreshDatabase;

    private function seedAndGenerate(): void
    {
        $this->seed(TeamSeeder::class);
        app(FixtureGenerationService::class)->generate();
    }

    public function test_reset_deletes_fixtures_but_keeps_teams(): void
    {
        $this->seedAndGenerate();
        $this->assertSame(12, Fixture::count());

        $this->postJson('/api/v1/league/reset')
            ->assertOk()
            ->assertJsonStructure(['message', 'data' => ['teams', 'fixtures', 'standings']])
            ->assertJsonPath('data.fixtures', []);

        $this->assertSame(0, Fixture::count());
        $this->assertSame(4, Team::count());
    }

    public function test_reset_response_has_zero_standings(): void
    {
        $this->seedAndGenerate();

        $response = $this->postJson('/api/v1/league/reset')->assertOk();

        $response->assertJsonCount(4, 'data.teams');
        $response->assertJsonCount(4, 'data.standings');
        $response->assertJsonCount(0, 'data.fixtures');

        foreach ($response->json('data.standings') as $standing) {
            $this->assertSame(0, $standing['played']);
            $this->assertSame(0, $standing['points']);
        }
    }

    public function test_reset_is_idempotent(): void
    {
        $this->seedAndGenerate();

        $this->postJson('/api/v1/league/reset')->assertOk();
        $this->postJson('/api/v1/league/reset')->assertOk();

        $this->assertSame(0, Fixture::count());
        $this->assertSame(4, Team::count());
    }

    public function test_reset_reseeds_teams_when_missing(): void
    {
        $this->assertSame(0, Team::count());

        $this->postJson('/api/v1/league/reset')
            ->assertOk()
            ->assertJsonCount(4, 'data.teams');

        $this->assertSame(4, Team::count());
    }

    public function test_demo_reset_command_clears_fixtures_and_keeps_teams(): void
    {
        $this->seedAndGenerate();
        $this->assertSame(12, Fixture::count());

        $this->artisan('league:demo-reset')->assertSuccessful();

        $this->assertSame(0, Fixture::count());
        $this->assertSame(4, Team::count());
    }
}
