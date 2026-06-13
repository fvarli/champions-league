<?php

namespace App\Services;

use App\Models\Team;

/**
 * Mutable accumulator used while building the league table. It collects a
 * single team's running totals and is converted to an immutable
 * {@see TeamStanding} once all fixtures have been processed.
 */
final class TeamTally
{
    public int $played = 0;

    public int $won = 0;

    public int $drawn = 0;

    public int $lost = 0;

    public int $goalsFor = 0;

    public int $goalsAgainst = 0;

    public function __construct(public readonly Team $team) {}

    public function record(int $scored, int $conceded): void
    {
        $this->played++;
        $this->goalsFor += $scored;
        $this->goalsAgainst += $conceded;

        if ($scored > $conceded) {
            $this->won++;
        } elseif ($scored < $conceded) {
            $this->lost++;
        } else {
            $this->drawn++;
        }
    }

    public function toStanding(): TeamStanding
    {
        return new TeamStanding(
            team: $this->team,
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn,
            lost: $this->lost,
            goalsFor: $this->goalsFor,
            goalsAgainst: $this->goalsAgainst,
        );
    }
}
