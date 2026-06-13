<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\LeagueStandingsService;
use App\Services\TeamStanding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueStandingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function team(string $name): Team
    {
        return Team::factory()->create(['name' => $name]);
    }

    private function play(Team $home, Team $away, int $homeScore, int $awayScore): void
    {
        Fixture::factory()->create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'played_at' => now(),
        ]);
    }

    /**
     * @param  list<TeamStanding>  $table
     */
    private function standingFor(array $table, Team $team): TeamStanding
    {
        foreach ($table as $standing) {
            if ($standing->team->id === $team->id) {
                return $standing;
            }
        }

        $this->fail("No standing found for team {$team->name}.");
    }

    /**
     * @return list<TeamStanding>
     */
    private function calculate(): array
    {
        return app(LeagueStandingsService::class)->calculate();
    }

    public function test_unplayed_fixtures_are_ignored(): void
    {
        $home = $this->team('Liverpool');
        $away = $this->team('Arsenal');

        // Unplayed: scores and kickoff are null (factory default).
        Fixture::factory()->create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);

        $table = $this->calculate();

        $this->assertSame(0, $this->standingFor($table, $home)->played);
        $this->assertSame(0, $this->standingFor($table, $away)->played);
        $this->assertSame(0, $this->standingFor($table, $home)->points());
    }

    public function test_home_win_awards_three_points_to_the_home_team(): void
    {
        $home = $this->team('Liverpool');
        $away = $this->team('Arsenal');

        $this->play($home, $away, 2, 1);

        $table = $this->calculate();

        $this->assertSame(3, $this->standingFor($table, $home)->points());
        $this->assertSame(1, $this->standingFor($table, $home)->won);
        $this->assertSame(0, $this->standingFor($table, $away)->points());
        $this->assertSame(1, $this->standingFor($table, $away)->lost);
    }

    public function test_away_win_awards_three_points_to_the_away_team(): void
    {
        $home = $this->team('Liverpool');
        $away = $this->team('Arsenal');

        $this->play($home, $away, 0, 2);

        $table = $this->calculate();

        $this->assertSame(3, $this->standingFor($table, $away)->points());
        $this->assertSame(1, $this->standingFor($table, $away)->won);
        $this->assertSame(0, $this->standingFor($table, $home)->points());
        $this->assertSame(1, $this->standingFor($table, $home)->lost);
    }

    public function test_draw_awards_one_point_to_each_team(): void
    {
        $home = $this->team('Liverpool');
        $away = $this->team('Arsenal');

        $this->play($home, $away, 1, 1);

        $table = $this->calculate();

        $this->assertSame(1, $this->standingFor($table, $home)->points());
        $this->assertSame(1, $this->standingFor($table, $home)->drawn);
        $this->assertSame(1, $this->standingFor($table, $away)->points());
        $this->assertSame(1, $this->standingFor($table, $away)->drawn);
    }

    public function test_goals_for_against_and_difference_are_calculated(): void
    {
        $home = $this->team('Liverpool');
        $away = $this->team('Arsenal');

        $this->play($home, $away, 3, 1);

        $table = $this->calculate();

        $homeRow = $this->standingFor($table, $home);
        $this->assertSame(3, $homeRow->goalsFor);
        $this->assertSame(1, $homeRow->goalsAgainst);
        $this->assertSame(2, $homeRow->goalDifference());

        $awayRow = $this->standingFor($table, $away);
        $this->assertSame(1, $awayRow->goalsFor);
        $this->assertSame(3, $awayRow->goalsAgainst);
        $this->assertSame(-2, $awayRow->goalDifference());
    }

    public function test_played_won_drawn_lost_are_calculated_across_several_fixtures(): void
    {
        $team = $this->team('Liverpool');
        $a = $this->team('Arsenal');
        $b = $this->team('Brighton');
        $c = $this->team('Chelsea');

        $this->play($team, $a, 2, 0); // win
        $this->play($b, $team, 1, 1);  // draw (away)
        $this->play($team, $c, 0, 1);  // loss

        $row = $this->standingFor($this->calculate(), $team);

        $this->assertSame(3, $row->played);
        $this->assertSame(1, $row->won);
        $this->assertSame(1, $row->drawn);
        $this->assertSame(1, $row->lost);
        $this->assertSame(4, $row->points());
    }

    public function test_teams_without_played_fixtures_appear_with_zero_values(): void
    {
        $teams = [
            $this->team('Liverpool'),
            $this->team('Arsenal'),
            $this->team('Chelsea'),
        ];

        $table = $this->calculate();

        $this->assertCount(3, $table);

        foreach ($teams as $team) {
            $row = $this->standingFor($table, $team);
            $this->assertSame(0, $row->played);
            $this->assertSame(0, $row->won);
            $this->assertSame(0, $row->drawn);
            $this->assertSame(0, $row->lost);
            $this->assertSame(0, $row->goalsFor);
            $this->assertSame(0, $row->goalsAgainst);
            $this->assertSame(0, $row->points());
        }
    }

    public function test_table_is_sorted_by_points_then_goal_difference_then_goals_for_then_name(): void
    {
        // Each focal team gets an independent record by beating a fresh filler.
        // Expected order exercises, in turn: points, goal difference, goals
        // for, and finally the team-name tiebreaker.
        $this->beatFiller('Yankee', 1, 0); // 3 pts (first win)
        $this->beatFiller('Yankee', 1, 0); // 6 pts total — highest points
        $this->beatFiller('Xray', 5, 0);   // 3 pts, GD +5
        $this->beatFiller('Tango', 6, 5);  // 3 pts, GD +1, GF 6
        $this->beatFiller('Romeo', 4, 3);  // 3 pts, GD +1, GF 4
        $this->beatFiller('Sierra', 4, 3); // 3 pts, GD +1, GF 4 — identical to Romeo

        $focal = ['Yankee', 'Xray', 'Tango', 'Romeo', 'Sierra'];

        $ordered = array_values(array_filter(
            array_map(fn (TeamStanding $row): string => $row->team->name, $this->calculate()),
            fn (string $name): bool => in_array($name, $focal, true),
        ));

        $this->assertSame($focal, $ordered);
    }

    private function beatFiller(string $name, int $for, int $against): void
    {
        $team = Team::query()->where('name', $name)->first() ?? $this->team($name);

        $this->play($team, $this->team("opponent of {$name} ({$for}-{$against})"), $for, $against);
    }
}
