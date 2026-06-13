<?php

namespace App\Actions;

use App\Exceptions\LeagueAlreadyCompleteException;
use App\Models\Fixture;
use Illuminate\Support\Collection;

/**
 * Plays the earliest week that still has unplayed fixtures.
 */
class PlayNextWeekAction
{
    public function __construct(
        private readonly PlayWeekAction $playWeek,
    ) {}

    /**
     * @return Collection<int, Fixture> The fixtures played by this call.
     *
     * @throws LeagueAlreadyCompleteException When no unplayed fixtures remain.
     */
    public function execute(): Collection
    {
        $week = Fixture::query()->whereNull('played_at')->min('week');

        if ($week === null) {
            throw LeagueAlreadyCompleteException::make();
        }

        return $this->playWeek->execute((int) $week);
    }
}
