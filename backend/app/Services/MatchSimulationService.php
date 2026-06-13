<?php

namespace App\Services;

use App\Exceptions\FixtureAlreadyPlayedException;
use App\Models\Fixture;
use Random\Randomizer;

/**
 * Simulates the result of a single unplayed fixture.
 *
 * Each team's strength is turned into a per-attempt scoring chance; the home
 * side gets a small advantage bonus. Goals come from a bounded probabilistic
 * process, so stronger teams score more often while variance keeps draws and
 * upsets possible. Randomness is injected so tests can seed it for stable runs.
 */
class MatchSimulationService
{
    /**
     * Strength bonus applied to the home side.
     */
    private const HOME_ADVANTAGE = 8;

    /**
     * Scales a team's strength share into a realistic finishing rate.
     */
    private const FINISHING_RATE = 0.6;

    /**
     * Scoring attempts per team, and therefore the maximum goals per team.
     */
    private const MAX_GOALS = 5;

    public function __construct(
        private readonly Randomizer $randomizer = new Randomizer,
    ) {}

    /**
     * Simulate the fixture, persist the result and return it.
     *
     * @throws FixtureAlreadyPlayedException When the fixture has already been played.
     */
    public function simulate(Fixture $fixture): Fixture
    {
        if ($fixture->played_at !== null) {
            throw FixtureAlreadyPlayedException::for($fixture);
        }

        $fixture->loadMissing(['homeTeam', 'awayTeam']);

        $homeAttack = $fixture->homeTeam->strength + self::HOME_ADVANTAGE;
        $awayAttack = $fixture->awayTeam->strength;

        $fixture->home_score = $this->generateGoals($this->scoringChance($homeAttack, $awayAttack));
        $fixture->away_score = $this->generateGoals($this->scoringChance($awayAttack, $homeAttack));
        $fixture->played_at = now();
        $fixture->save();

        return $fixture;
    }

    /**
     * A team's chance (as a percentage) of scoring on a single attempt,
     * based on its attacking strength relative to the opponent's.
     */
    private function scoringChance(int $attack, int $defence): int
    {
        $share = $attack / ($attack + $defence);

        return (int) round($share * self::FINISHING_RATE * 100);
    }

    /**
     * Draw goals from a bounded sequence of independent scoring attempts.
     */
    private function generateGoals(int $chancePercent): int
    {
        $goals = 0;

        for ($attempt = 0; $attempt < self::MAX_GOALS; $attempt++) {
            if ($this->randomizer->getInt(1, 100) <= $chancePercent) {
                $goals++;
            }
        }

        return $goals;
    }
}
