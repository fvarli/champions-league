<?php

namespace App\Services;

use App\Exceptions\PredictionNotAvailableException;
use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Support\Collection;

/**
 * Estimates each team's chance of finishing the league first.
 *
 * The estimate is a Monte Carlo simulation: played fixtures are kept exactly
 * as they are, the remaining fixtures are simulated many times using the same
 * strength/home-advantage logic as live matches, and the share of runs each
 * team tops the table becomes its championship percentage.
 *
 * Nothing is ever persisted during prediction.
 */
class ChampionshipPredictionService
{
    /**
     * Played fixtures required before a prediction is offered.
     */
    private const MIN_PLAYED_FIXTURES = 8;

    public function __construct(
        private readonly MatchSimulationService $simulation,
        private readonly LeagueStandingsService $standings,
        private readonly int $iterations = 1000,
    ) {}

    /**
     * @return list<ChampionChance> Sorted from most to least likely champion.
     *
     * @throws PredictionNotAvailableException When too few fixtures have been played.
     */
    public function predict(): array
    {
        $teams = Team::query()->orderBy('name')->get();

        $played = Fixture::query()
            ->whereNotNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        $remaining = Fixture::query()
            ->whereNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        if ($remaining->isEmpty()) {
            return $this->completedLeague($teams, $this->toResults($played));
        }

        if ($played->count() < self::MIN_PLAYED_FIXTURES) {
            throw PredictionNotAvailableException::needsMorePlayedFixtures(
                $played->count(),
                self::MIN_PLAYED_FIXTURES,
            );
        }

        return $this->runSimulations($teams, $this->toResults($played), $remaining);
    }

    /**
     * A finished league has a known champion: 100% to first, 0% to the rest.
     *
     * @param  Collection<int, Team>  $teams
     * @param  Collection<int, MatchResult>  $playedResults
     * @return list<ChampionChance>
     */
    private function completedLeague(Collection $teams, Collection $playedResults): array
    {
        $table = $this->standings->tableFor($teams, $playedResults);
        $leader = $table[0] ?? null;

        $percentages = $leader === null ? [] : [$leader->team->id => 100.0];

        return $this->buildChances($teams, $percentages);
    }

    /**
     * @param  Collection<int, Team>  $teams
     * @param  Collection<int, MatchResult>  $playedResults
     * @param  Collection<int, Fixture>  $remaining
     * @return list<ChampionChance>
     */
    private function runSimulations(Collection $teams, Collection $playedResults, Collection $remaining): array
    {
        $wins = [];
        foreach ($teams as $team) {
            $wins[$team->id] = 0;
        }

        for ($run = 0; $run < $this->iterations; $run++) {
            $results = $playedResults->merge($this->simulateRemaining($remaining));
            $table = $this->standings->tableFor($teams, $results);

            $leader = $table[0] ?? null;
            if ($leader !== null) {
                $wins[$leader->team->id]++;
            }
        }

        $percentages = [];
        foreach ($wins as $teamId => $count) {
            $percentages[$teamId] = $count / $this->iterations * 100;
        }

        return $this->buildChances($teams, $percentages);
    }

    /**
     * Simulate each remaining fixture once, in memory.
     *
     * @param  Collection<int, Fixture>  $remaining
     * @return Collection<int, MatchResult>
     */
    private function simulateRemaining(Collection $remaining): Collection
    {
        return $remaining->map(function (Fixture $fixture): MatchResult {
            [$homeScore, $awayScore] = $this->simulation->generateScore(
                $fixture->homeTeam->strength,
                $fixture->awayTeam->strength,
            );

            return new MatchResult($fixture->homeTeam, $fixture->awayTeam, $homeScore, $awayScore);
        });
    }

    /**
     * @param  Collection<int, Fixture>  $fixtures
     * @return Collection<int, MatchResult>
     */
    private function toResults(Collection $fixtures): Collection
    {
        return $fixtures->map(fn (Fixture $fixture): MatchResult => new MatchResult(
            $fixture->homeTeam,
            $fixture->awayTeam,
            (int) $fixture->home_score,
            (int) $fixture->away_score,
        ));
    }

    /**
     * Map percentages onto every team and sort by likelihood, then name.
     *
     * @param  Collection<int, Team>  $teams
     * @param  array<int, float>  $percentages
     * @return list<ChampionChance>
     */
    private function buildChances(Collection $teams, array $percentages): array
    {
        $chances = $teams
            ->map(fn (Team $team): ChampionChance => new ChampionChance($team, $percentages[$team->id] ?? 0.0))
            ->all();

        usort(
            $chances,
            fn (ChampionChance $a, ChampionChance $b): int => [$b->percentage, $a->team->name] <=> [$a->percentage, $b->team->name],
        );

        return $chances;
    }
}
