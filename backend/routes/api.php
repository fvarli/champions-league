<?php

use App\Http\Controllers\Api\LeagueController;
use Illuminate\Support\Facades\Route;

Route::get('teams', [LeagueController::class, 'teams']);

Route::get('fixtures', [LeagueController::class, 'fixtures']);
Route::post('fixtures/generate', [LeagueController::class, 'generate']);

Route::get('standings', [LeagueController::class, 'standings']);

Route::post('weeks/next/play', [LeagueController::class, 'playNextWeek']);
Route::post('weeks/{week}/play', [LeagueController::class, 'playWeek'])->whereNumber('week');

Route::post('league/play-all', [LeagueController::class, 'playAll']);

Route::get('predictions', [LeagueController::class, 'predictions']);
