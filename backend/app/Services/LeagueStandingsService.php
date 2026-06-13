<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Team;

/**
 * Builds the league table from played fixtures.
 *
 * The result is computed on the fly and never persisted: standings are always
 * derived from the current fixtures. Only played fixtures (both scores and a
 * kickoff time set) contribute; unplayed fixtures are ignored.
 */
class LeagueStandingsService
{
    /**
     * Calculate the current league table, sorted from first to last.
     *
     * @return list<TeamStanding>
     */
    public function calculate(): array
    {
        $teams = Team::query()->orderBy('name')->get();

        $results = Fixture::query()
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->whereNotNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get()
            ->map(fn (Fixture $fixture): MatchResult => new MatchResult(
                $fixture->homeTeam,
                $fixture->awayTeam,
                (int) $fixture->home_score,
                (int) $fixture->away_score,
            ));

        return $this->tableFor($teams, $results);
    }

    /**
     * Build a sorted league table from an arbitrary set of results.
     *
     * This is database-agnostic: callers supply the teams and the results to
     * tally (stored fixtures, simulated outcomes, or a mix), which lets the
     * same sorting rules drive both live standings and prediction.
     *
     * @param  iterable<Team>  $teams
     * @param  iterable<MatchResult>  $results
     * @return list<TeamStanding>
     */
    public function tableFor(iterable $teams, iterable $results): array
    {
        $tallies = [];

        foreach ($teams as $team) {
            $tallies[$team->id] = new TeamTally($team);
        }

        foreach ($results as $result) {
            $tallies[$result->home->id]->record($result->homeScore, $result->awayScore);
            $tallies[$result->away->id]->record($result->awayScore, $result->homeScore);
        }

        $standings = array_map(
            fn (TeamTally $tally): TeamStanding => $tally->toStanding(),
            array_values($tallies),
        );

        usort($standings, $this->comparator(...));

        return $standings;
    }

    /**
     * Order by points, then goal difference, then goals for, then name.
     */
    private function comparator(TeamStanding $a, TeamStanding $b): int
    {
        return [$b->points(), $b->goalDifference(), $b->goalsFor, $a->team->name]
            <=> [$a->points(), $a->goalDifference(), $a->goalsFor, $b->team->name];
    }
}
