<?php

use App\Http\Controllers\Api\ChampionsLeagueApiController;
use App\Http\Controllers\Api\GameMatchController;
use App\Http\Controllers\Api\LeagueStandingController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Champions League API Routes
Route::prefix('champions-league')->name('api.champions-league.')->group(function () {
    // Teams - Full CRUD
    Route::apiResource('teams', TeamController::class);
    
    // Matches - Full CRUD + Custom endpoints
    Route::apiResource('matches', GameMatchController::class);
    Route::get('/matches/by-week', [GameMatchController::class, 'byWeek'])->name('matches.by-week');
    
    // Standings - Full CRUD
    Route::apiResource('standings', LeagueStandingController::class);
    
    // League Actions
    Route::post('/matches/reset', [ChampionsLeagueApiController::class, 'resetMatches'])->name('matches.reset');
    Route::post('/matches/play-all', [ChampionsLeagueApiController::class, 'playAllMatches'])->name('matches.play-all');
    Route::post('/matches/play-week', [ChampionsLeagueApiController::class, 'playWeek'])->name('matches.play-week');
});
