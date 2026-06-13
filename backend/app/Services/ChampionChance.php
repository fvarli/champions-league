<?php

namespace App\Services;

use App\Models\Team;

/**
 * A team's estimated chance (as a percentage) of finishing the league first.
 */
final class ChampionChance
{
    public function __construct(
        public readonly Team $team,
        public readonly float $percentage,
    ) {}
}
