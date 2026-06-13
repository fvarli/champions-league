<?php

namespace Tests\Feature;

use App\Exceptions\FixtureGenerationException;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\FixtureGenerationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function generateForFourTeams(): void
    {
        Team::factory()->count(4)->create();

        app(FixtureGenerationService::class)->generate();
    }

    public function test_it_generates_exactly_twelve_fixtures(): void
    {
        $this->generateForFourTeams();

        $this->assertSame(12, Fixture::count());
    }

    public function test_it_generates_exactly_six_weeks(): void
    {
        $this->generateForFourTeams();

        $weeks = Fixture::query()->distinct()->pluck('week');

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6], $weeks->all());
    }

    public function test_each_week_contains_exactly_two_matches(): void
    {
        $this->generateForFourTeams();

        $countsByWeek = Fixture::all()->countBy('week');

        $this->assertCount(6, $countsByWeek);
        foreach ($countsByWeek as $count) {
            $this->assertSame(2, $count);
        }
    }

    public function test_each_team_plays_exactly_once_per_week(): void
    {
        $this->generateForFourTeams();

        $teamIds = Team::query()->orderBy('id')->pluck('id')->all();

        Fixture::all()->groupBy('week')->each(function (Collection $weekFixtures) use ($teamIds): void {
            $playing = $weekFixtures
                ->flatMap(fn (Fixture $fixture): array => [$fixture->home_team_id, $fixture->away_team_id])
                ->sort()
                ->values()
                ->all();

            $this->assertEqualsCanonicalizing($teamIds, $playing);
        });
    }

    public function test_every_pair_plays_twice_with_one_home_and_one_away(): void
    {
        $this->generateForFourTeams();

        $fixtures = Fixture::all();

        $teamIds = Team::query()->orderBy('id')->pluck('id')->all();

        foreach ($teamIds as $a) {
            foreach ($teamIds as $b) {
                if ($a >= $b) {
                    continue;
                }

                $aHome = $fixtures->where('home_team_id', $a)->where('away_team_id', $b)->count();
                $bHome = $fixtures->where('home_team_id', $b)->where('away_team_id', $a)->count();

                $this->assertSame(1, $aHome, "Expected one fixture with team {$a} at home against {$b}.");
                $this->assertSame(1, $bHome, "Expected one fixture with team {$b} at home against {$a}.");
            }
        }
    }

    public function test_generated_fixtures_start_unplayed(): void
    {
        $this->generateForFourTeams();

        $this->assertSame(12, Fixture::query()
            ->whereNull('home_score')
            ->whereNull('away_score')
            ->whereNull('played_at')
            ->count());
    }

    public function test_running_generation_twice_fails_and_does_not_duplicate(): void
    {
        $this->generateForFourTeams();

        try {
            app(FixtureGenerationService::class)->generate();
            $this->fail('Expected a FixtureGenerationException on the second run.');
        } catch (FixtureGenerationException $e) {
            $this->assertSame('Fixtures have already been generated.', $e->getMessage());
        }

        $this->assertSame(12, Fixture::count());
    }

    public function test_generation_fails_when_team_count_is_not_four(): void
    {
        Team::factory()->count(3)->create();

        $this->expectException(FixtureGenerationException::class);

        app(FixtureGenerationService::class)->generate();
    }
}
