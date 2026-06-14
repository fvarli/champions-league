<?php

namespace App\Http\Controllers\Api;

use App\Actions\PlayAllRemainingFixturesAction;
use App\Actions\PlayNextWeekAction;
use App\Actions\PlayWeekAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\FixtureResource;
use App\Http\Resources\PredictionResource;
use App\Http\Resources\StandingResource;
use App\Http\Resources\TeamResource;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\ChampionshipPredictionService;
use App\Services\DemoResetService;
use App\Services\FixtureGenerationService;
use App\Services\LeagueStandingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class LeagueController extends Controller
{
    public function teams(): AnonymousResourceCollection
    {
        return TeamResource::collection(Team::query()->orderBy('id')->get());
    }

    public function fixtures(): JsonResponse
    {
        $fixtures = Fixture::query()
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $byWeek = [];
        foreach ($fixtures as $fixture) {
            $byWeek[$fixture->week][] = new FixtureResource($fixture);
        }

        return response()->json(['data' => $byWeek]);
    }

    public function standings(LeagueStandingsService $standings): AnonymousResourceCollection
    {
        return StandingResource::collection($standings->calculate());
    }

    public function generate(FixtureGenerationService $service): JsonResponse
    {
        $service->generate();

        $fixtures = Fixture::query()
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Fixtures generated.',
            'data' => FixtureResource::collection($fixtures)->resolve(),
        ], 201);
    }

    public function playWeek(int $week, PlayWeekAction $action): JsonResponse
    {
        $played = $action->execute($week);

        return response()->json([
            'message' => "Week {$week} played.",
            'data' => FixtureResource::collection($played)->resolve(),
        ]);
    }

    public function playNextWeek(PlayNextWeekAction $action): JsonResponse
    {
        $played = $action->execute();
        $week = $played->first()?->week;

        return response()->json([
            'message' => "Week {$week} played.",
            'data' => FixtureResource::collection($played)->resolve(),
        ]);
    }

    public function playAll(PlayAllRemainingFixturesAction $action): JsonResponse
    {
        $byWeek = $action->execute()->map(
            fn (Collection $fixtures): array => FixtureResource::collection($fixtures)->resolve(),
        );

        return response()->json([
            'message' => 'All remaining fixtures played.',
            'data' => $byWeek,
        ]);
    }

    public function predictions(ChampionshipPredictionService $service): AnonymousResourceCollection
    {
        return PredictionResource::collection($service->predict());
    }

    public function reset(DemoResetService $reset, LeagueStandingsService $standings): JsonResponse
    {
        $reset->reset();

        $teams = Team::query()->orderBy('id')->get();

        return response()->json([
            'message' => 'League reset to its initial state.',
            'data' => [
                'teams' => TeamResource::collection($teams)->resolve(),
                'fixtures' => [],
                'standings' => StandingResource::collection($standings->calculate())->resolve(),
            ],
        ]);
    }
}
