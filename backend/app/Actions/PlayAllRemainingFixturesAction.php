<?php

namespace App\Actions;

use App\Exceptions\LeagueAlreadyCompleteException;
use App\Models\Fixture;
use Illuminate\Support\Collection;

/**
 * Plays every remaining week in order, leaving already played fixtures intact.
 */
class PlayAllRemainingFixturesAction
{
    public function __construct(
        private readonly PlayWeekAction $playWeek,
    ) {}

    /**
     * @return Collection<int, Collection<int, Fixture>> Played fixtures keyed by week.
     *
     * @throws LeagueAlreadyCompleteException When no unplayed fixtures remain.
     */
    public function execute(): Collection
    {
        $weeks = $this->remainingWeeks();

        if ($weeks->isEmpty()) {
            throw LeagueAlreadyCompleteException::make();
        }

        $playedByWeek = new Collection;

        foreach ($weeks as $week) {
            $playedByWeek->put($week, $this->playWeek->execute($week));
        }

        return $playedByWeek;
    }

    /**
     * Week numbers that still contain unplayed fixtures, in ascending order.
     *
     * @return Collection<int, int>
     */
    private function remainingWeeks(): Collection
    {
        return Fixture::query()
            ->whereNull('played_at')
            ->distinct()
            ->orderBy('week')
            ->pluck('week')
            ->map(fn (mixed $week): int => (int) $week);
    }
}
