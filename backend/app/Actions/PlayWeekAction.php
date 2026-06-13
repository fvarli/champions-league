<?php

namespace App\Actions;

use App\Exceptions\InvalidWeekException;
use App\Exceptions\WeekAlreadyPlayedException;
use App\Models\Fixture;
use App\Services\MatchSimulationService;
use Illuminate\Support\Collection;

/**
 * Plays a single week by simulating every unplayed fixture in it.
 */
class PlayWeekAction
{
    public function __construct(
        private readonly MatchSimulationService $simulation,
    ) {}

    /**
     * @return Collection<int, Fixture> The fixtures played by this call.
     *
     * @throws InvalidWeekException When the week has no fixtures.
     * @throws WeekAlreadyPlayedException When every fixture in the week is already played.
     */
    public function execute(int $week): Collection
    {
        $fixtures = Fixture::query()->where('week', $week)->orderBy('id')->get();

        if ($fixtures->isEmpty()) {
            throw InvalidWeekException::for($week);
        }

        $unplayed = $fixtures->whereNull('played_at');

        if ($unplayed->isEmpty()) {
            throw WeekAlreadyPlayedException::for($week);
        }

        return $unplayed
            ->map(fn (Fixture $fixture): Fixture => $this->simulation->simulate($fixture))
            ->values();
    }
}
