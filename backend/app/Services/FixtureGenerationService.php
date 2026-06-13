<?php

namespace App\Services;

use App\Exceptions\FixtureGenerationException;
use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

/**
 * Generates the league's full double round-robin schedule.
 *
 * With four teams this yields 12 fixtures across 6 weeks: every pair meets
 * twice, once at home and once away, and each team plays once per week.
 */
class FixtureGenerationService
{
    /**
     * The number of teams the league format requires.
     */
    private const REQUIRED_TEAMS = 4;

    /**
     * Build and persist the full fixture schedule.
     *
     * @throws FixtureGenerationException When the team count is wrong or
     *                                    fixtures already exist.
     */
    public function generate(): void
    {
        $teams = Team::query()->orderBy('id')->get();

        if ($teams->count() !== self::REQUIRED_TEAMS) {
            throw FixtureGenerationException::invalidTeamCount($teams->count());
        }

        if (Fixture::query()->exists()) {
            throw FixtureGenerationException::alreadyGenerated();
        }

        $schedule = $this->buildSchedule($teams->values()->all());

        DB::transaction(function () use ($schedule): void {
            foreach ($schedule as $weekIndex => $pairings) {
                $week = $weekIndex + 1;

                foreach ($pairings as [$home, $away]) {
                    Fixture::create([
                        'week' => $week,
                        'home_team_id' => $home->id,
                        'away_team_id' => $away->id,
                    ]);
                }
            }
        });
    }

    /**
     * Build the week-by-week schedule using the circle method.
     *
     * The first team is fixed while the rest rotate, producing the first leg;
     * the second leg repeats the pairings with home and away swapped.
     *
     * @param  array<int, Team>  $teams
     * @return array<int, array<int, array{0: Team, 1: Team}>>
     */
    private function buildSchedule(array $teams): array
    {
        $count = count($teams);
        $rounds = $count - 1;
        $matchesPerRound = intdiv($count, 2);

        $firstLeg = [];

        for ($round = 0; $round < $rounds; $round++) {
            $order = $this->rotationOrder($round, $count);

            $week = [];
            for ($i = 0; $i < $matchesPerRound; $i++) {
                $home = $teams[$order[$i]];
                $away = $teams[$order[$count - 1 - $i]];
                $week[] = [$home, $away];
            }

            $firstLeg[] = $week;
        }

        $secondLeg = [];
        foreach ($firstLeg as $week) {
            $secondLeg[] = array_map(
                static fn (array $pairing): array => [$pairing[1], $pairing[0]],
                $week,
            );
        }

        return array_merge($firstLeg, $secondLeg);
    }

    /**
     * Team indexes for a given round: index 0 stays fixed, the rest rotate.
     *
     * @return array<int, int>
     */
    private function rotationOrder(int $round, int $count): array
    {
        $order = [0];

        for ($position = 1; $position < $count; $position++) {
            $order[$position] = 1 + $this->mod($position - 1 - $round, $count - 1);
        }

        return $order;
    }

    /**
     * Modulo that always returns a non-negative result.
     */
    private function mod(int $value, int $modulus): int
    {
        return (($value % $modulus) + $modulus) % $modulus;
    }
}
