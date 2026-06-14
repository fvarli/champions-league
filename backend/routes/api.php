<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\LeagueController;
use App\Support\ApiVersion;
use Illuminate\Support\Facades\Route;

// All public endpoints live under a version prefix (e.g. /api/v1). Future
// breaking changes are introduced under a new prefix (/api/v2) so existing
// clients keep working. See App\Support\ApiVersion for the current version.
Route::prefix(ApiVersion::CURRENT)->group(function () {
    Route::get('health', HealthController::class);

    Route::get('teams', [LeagueController::class, 'teams']);

    Route::get('fixtures', [LeagueController::class, 'fixtures']);
    Route::post('fixtures/generate', [LeagueController::class, 'generate']);
    Route::patch('fixtures/{fixture}/score', [LeagueController::class, 'updateScore']);

    Route::get('standings', [LeagueController::class, 'standings']);

    Route::post('weeks/next/play', [LeagueController::class, 'playNextWeek']);
    Route::post('weeks/{week}/play', [LeagueController::class, 'playWeek'])->whereNumber('week');

    Route::post('league/play-all', [LeagueController::class, 'playAll']);
    Route::post('league/reset', [LeagueController::class, 'reset']);

    Route::get('predictions', [LeagueController::class, 'predictions']);
});
