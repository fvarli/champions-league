<?php

namespace App\Services;

use App\Models\Team;

/**
 * Immutable view of a single team's record in the league table.
 */
final class TeamStanding
{
    public function __construct(
        public readonly Team $team,
        public readonly int $played,
        public readonly int $won,
        public readonly int $drawn,
        public readonly int $lost,
        public readonly int $goalsFor,
        public readonly int $goalsAgainst,
    ) {}

    public function goalDifference(): int
    {
        return $this->goalsFor - $this->goalsAgainst;
    }

    public function points(): int
    {
        return ($this->won * 3) + $this->drawn;
    }
}
