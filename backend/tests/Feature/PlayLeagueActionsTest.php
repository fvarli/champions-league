<?php

namespace Tests\Feature;

use App\Actions\PlayAllRemainingFixturesAction;
use App\Actions\PlayNextWeekAction;
use App\Actions\PlayWeekAction;
use App\Exceptions\InvalidWeekException;
use App\Exceptions\LeagueAlreadyCompleteException;
use App\Exceptions\WeekAlreadyPlayedException;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\FixtureGenerationService;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

class PlayLeagueActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Deterministic simulation so the orchestration tests never flake.
        $this->app->instance(
            MatchSimulationService::class,
            new MatchSimulationService(new Randomizer(new Mt19937(12345))),
        );
    }

    private function generateLeague(): void
    {
        Team::factory()->count(4)->create();
        app(FixtureGenerationService::class)->generate();
    }

    public function test_a_specific_week_can_be_played(): void
    {
        $this->generateLeague();

        $played = app(PlayWeekAction::class)->execute(3);

        $this->assertCount(2, $played);
        $this->assertTrue($played->every(fn (Fixture $fixture): bool => $fixture->week === 3));
    }

    public function test_playing_a_week_simulates_exactly_two_fixtures(): void
    {
        $this->generateLeague();

        app(PlayWeekAction::class)->execute(1);

        $this->assertSame(2, Fixture::query()->where('week', 1)->whereNotNull('played_at')->count());
        $this->assertSame(10, Fixture::query()->whereNull('played_at')->count());
    }

    public function test_played_fixtures_receive_scores_and_played_at(): void
    {
        $this->generateLeague();

        $played = app(PlayWeekAction::class)->execute(2);

        foreach ($played as $fixture) {
            $this->assertNotNull($fixture->home_score);
            $this->assertNotNull($fixture->away_score);
            $this->assertNotNull($fixture->played_at);
        }
    }

    public function test_playing_an_already_completed_week_fails_clearly(): void
    {
        $this->generateLeague();
        app(PlayWeekAction::class)->execute(2);

        $this->expectException(WeekAlreadyPlayedException::class);

        app(PlayWeekAction::class)->execute(2);
    }

    public function test_playing_a_missing_week_fails_clearly(): void
    {
        $this->generateLeague();

        $this->expectException(InvalidWeekException::class);

        app(PlayWeekAction::class)->execute(7);
    }

    public function test_playing_next_week_selects_the_earliest_unplayed_week(): void
    {
        $this->generateLeague();

        // Play a later week first; the next-week action must still pick week 1.
        app(PlayWeekAction::class)->execute(3);

        $played = app(PlayNextWeekAction::class)->execute();

        $this->assertTrue($played->every(fn (Fixture $fixture): bool => $fixture->week === 1));
    }

    public function test_playing_all_completes_all_remaining_fixtures(): void
    {
        $this->generateLeague();

        $byWeek = app(PlayAllRemainingFixturesAction::class)->execute();

        $this->assertSame([1, 2, 3, 4, 5, 6], $byWeek->keys()->all());
        $this->assertSame(12, $byWeek->flatten()->count());
        $this->assertSame(0, Fixture::query()->whereNull('played_at')->count());
    }

    public function test_playing_all_preserves_already_played_fixtures(): void
    {
        $this->generateLeague();

        $week1 = app(PlayWeekAction::class)->execute(1);

        $byWeek = app(PlayAllRemainingFixturesAction::class)->execute();

        // Week 1 is not replayed and the remaining 10 fixtures are completed.
        $this->assertFalse($byWeek->has(1));
        $this->assertSame(10, $byWeek->flatten()->count());

        foreach ($week1 as $fixture) {
            $fresh = $fixture->fresh();
            $this->assertSame($fixture->home_score, $fresh->home_score);
            $this->assertSame($fixture->away_score, $fresh->away_score);
            $this->assertEquals($fixture->played_at, $fresh->played_at);
        }
    }

    public function test_playing_all_fails_when_the_league_is_already_complete(): void
    {
        $this->generateLeague();
        app(PlayAllRemainingFixturesAction::class)->execute();

        $this->expectException(LeagueAlreadyCompleteException::class);

        app(PlayAllRemainingFixturesAction::class)->execute();
    }

    public function test_playing_next_week_fails_when_the_league_is_already_complete(): void
    {
        $this->generateLeague();
        app(PlayAllRemainingFixturesAction::class)->execute();

        $this->expectException(LeagueAlreadyCompleteException::class);

        app(PlayNextWeekAction::class)->execute();
    }
}
