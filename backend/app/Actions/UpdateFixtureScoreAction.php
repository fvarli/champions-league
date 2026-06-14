<?php

namespace App\Actions;

use App\Models\Fixture;

/**
 * Updates a single fixture's score. An unplayed fixture becomes played
 * (stamped now); an already played fixture keeps its original kickoff time.
 * Standings and predictions are always derived from fixtures, so editing a
 * score is enough to change them — nothing else is touched.
 */
class UpdateFixtureScoreAction
{
    public function execute(Fixture $fixture, int $homeScore, int $awayScore): Fixture
    {
        $fixture->home_score = $homeScore;
        $fixture->away_score = $awayScore;

        if ($fixture->played_at === null) {
            $fixture->played_at = now();
        }

        $fixture->save();
        $fixture->loadMissing(['homeTeam', 'awayTeam']);

        return $fixture;
    }
}
