<?php

namespace Tests\Feature;

use App\Actions\PlayWeekAction;
use App\Exceptions\PredictionNotAvailableException;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\ChampionChance;
use App\Services\ChampionshipPredictionService;
use App\Services\FixtureGenerationService;
use App\Services\LeagueStandingsService;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

class ChampionshipPredictionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Deterministic simulation while building up the played league state.
        $this->app->instance(
            MatchSimulationService::class,
            new MatchSimulationService(new Randomizer(new Mt19937(777))),
        );
    }

    private function predictionService(int $seed = 1, int $iterations = 200): ChampionshipPredictionService
    {
        return new ChampionshipPredictionService(
            new MatchSimulationService(new Randomizer(new Mt19937($seed))),
            new LeagueStandingsService,
            $iterations,
        );
    }

    private function setUpLeague(): void
    {
        Team::factory()->create(['name' => 'Liverpool', 'strength' => 90]);
        Team::factory()->create(['name' => 'Manchester City', 'strength' => 88]);
        Team::factory()->create(['name' => 'Chelsea', 'strength' => 82]);
        Team::factory()->create(['name' => 'Arsenal', 'strength' => 80]);

        app(FixtureGenerationService::class)->generate();
    }

    private function playWeeks(int $upTo): void
    {
        for ($week = 1; $week <= $upTo; $week++) {
            app(PlayWeekAction::class)->execute($week);
        }
    }

    /**
     * @return array<int, array{id: int, home_score: int|null, away_score: int|null, played: bool}>
     */
    private function fixtureSnapshot(): array
    {
        return Fixture::query()->orderBy('id')->get()->map(fn (Fixture $f): array => [
            'id' => $f->id,
            'home_score' => $f->home_score,
            'away_score' => $f->away_score,
            'played' => $f->played_at !== null,
        ])->all();
    }

    private function playedFixture(Team $home, Team $away, int $homeScore, int $awayScore): void
    {
        Fixture::factory()->create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'played_at' => now(),
        ]);
    }

    private function pendingFixture(Team $home, Team $away): void
    {
        Fixture::factory()->create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);
    }

    private function totalPercentage(array $chances): float
    {
        return array_sum(array_map(fn (ChampionChance $chance): float => $chance->percentage, $chances));
    }

    public function test_prediction_returns_one_row_per_team(): void
    {
        $this->setUpLeague();
        $this->playWeeks(4);

        $chances = $this->predictionService()->predict();

        $this->assertCount(4, $chances);
    }

    public function test_percentages_total_one_hundred(): void
    {
        $this->setUpLeague();
        $this->playWeeks(4);

        $chances = $this->predictionService(seed: 5)->predict();

        $this->assertEqualsWithDelta(100.0, $this->totalPercentage($chances), 0.0001);
    }

    public function test_prediction_does_not_modify_persisted_fixtures(): void
    {
        $this->setUpLeague();
        $this->playWeeks(4);

        $before = $this->fixtureSnapshot();

        $this->predictionService()->predict();

        $this->assertSame($before, $this->fixtureSnapshot());
        $this->assertSame(4, Fixture::query()->whereNull('played_at')->count());
    }

    public function test_completed_league_gives_champion_one_hundred_percent(): void
    {
        $this->setUpLeague();
        $this->playWeeks(6);

        $table = app(LeagueStandingsService::class)->calculate();
        $championId = $table[0]->team->id;

        $chances = $this->predictionService()->predict();

        foreach ($chances as $chance) {
            $expected = $chance->team->id === $championId ? 100.0 : 0.0;
            $this->assertSame($expected, $chance->percentage);
        }
    }

    public function test_handles_no_remaining_fixtures(): void
    {
        $this->setUpLeague();
        $this->playWeeks(6);

        $chances = $this->predictionService()->predict();

        $this->assertCount(4, $chances);
        $this->assertEqualsWithDelta(100.0, $this->totalPercentage($chances), 0.0001);
        $this->assertSame(100.0, $chances[0]->percentage);
    }

    public function test_a_dominant_leader_has_the_highest_percentage(): void
    {
        $liverpool = Team::factory()->create(['name' => 'Liverpool', 'strength' => 95]);
        $b = Team::factory()->create(['name' => 'Burnley', 'strength' => 68]);
        $c = Team::factory()->create(['name' => 'Crystal Palace', 'strength' => 68]);
        $d = Team::factory()->create(['name' => 'Brentford', 'strength' => 68]);

        // Liverpool wins everything; the rest only draw. Eight fixtures played.
        $this->playedFixture($liverpool, $b, 4, 0);
        $this->playedFixture($liverpool, $c, 4, 0);
        $this->playedFixture($liverpool, $d, 4, 0);
        $this->playedFixture($b, $liverpool, 0, 4);
        $this->playedFixture($b, $c, 0, 0);
        $this->playedFixture($c, $d, 1, 1);
        $this->playedFixture($b, $d, 1, 1);
        $this->playedFixture($c, $b, 0, 0);

        // Two fixtures remain.
        $this->pendingFixture($c, $d);
        $this->pendingFixture($b, $liverpool);

        $chances = $this->predictionService()->predict();

        $this->assertSame('Liverpool', $chances[0]->team->name);
        $this->assertGreaterThan($chances[1]->percentage, $chances[0]->percentage);
    }

    public function test_prediction_works_with_partial_league_state(): void
    {
        $this->setUpLeague();
        $this->playWeeks(5); // 10 played, 2 remaining

        $chances = $this->predictionService()->predict();

        $this->assertCount(4, $chances);
        $this->assertEqualsWithDelta(100.0, $this->totalPercentage($chances), 0.0001);

        foreach ($chances as $chance) {
            $this->assertGreaterThanOrEqual(0.0, $chance->percentage);
            $this->assertLessThanOrEqual(100.0, $chance->percentage);
        }
    }

    public function test_prediction_refuses_when_fewer_than_eight_fixtures_played(): void
    {
        $this->setUpLeague();
        $this->playWeeks(3); // 6 played, 6 remaining

        $this->expectException(PredictionNotAvailableException::class);

        $this->predictionService()->predict();
    }
}
