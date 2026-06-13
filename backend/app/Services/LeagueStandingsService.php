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
        $tallies = Team::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Team $team): array => [$team->id => new TeamTally($team)]);

        $fixtures = Fixture::query()
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->whereNotNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        foreach ($fixtures as $fixture) {
            $homeScore = (int) $fixture->home_score;
            $awayScore = (int) $fixture->away_score;

            $tallies[$fixture->homeTeam->id]->record($homeScore, $awayScore);
            $tallies[$fixture->awayTeam->id]->record($awayScore, $homeScore);
        }

        $standings = $tallies
            ->map(fn (TeamTally $tally): TeamStanding => $tally->toStanding())
            ->values()
            ->all();

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
