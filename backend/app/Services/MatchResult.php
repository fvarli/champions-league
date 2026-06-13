<?php

namespace App\Services;

use App\Models\Team;

/**
 * A single match outcome used to build a league table. Decoupled from the
 * persistence layer so standings can be computed for hypothetical results
 * (for example during championship prediction) as well as stored fixtures.
 */
final class MatchResult
{
    public function __construct(
        public readonly Team $home,
        public readonly Team $away,
        public readonly int $homeScore,
        public readonly int $awayScore,
    ) {}
}
