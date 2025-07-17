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

Route::prefix('champions-league')->name('api.champions-league.')->group(function () {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::prefix('matches')->name('matches.')->group(function () {
        Route::get('/by-week', [GameMatchController::class, 'byWeek'])->name('by-week');
        Route::post('/reset', [ChampionsLeagueApiController::class, 'resetMatches'])->name('reset');
        Route::post('/play-all', [ChampionsLeagueApiController::class, 'playAllMatches'])->name('play-all');
        Route::post('/play-week', [ChampionsLeagueApiController::class, 'playWeek'])->name('play-week');

        Route::get('/', [GameMatchController::class, 'index'])->name('index');
        Route::post('/', [GameMatchController::class, 'store'])->name('store');
        Route::get('/{match}', [GameMatchController::class, 'show'])->name('show');
        Route::put('/{match}', [GameMatchController::class, 'update'])->name('update');
        Route::delete('/{match}', [GameMatchController::class, 'destroy'])->name('destroy');
    });

    // Standings routes
    Route::get('/standings', [LeagueStandingController::class, 'index'])->name('standings.index');
    Route::post('/standings', [LeagueStandingController::class, 'store'])->name('standings.store');
    Route::get('/standings/{standing}', [LeagueStandingController::class, 'show'])->name('standings.show');
    Route::put('/standings/{standing}', [LeagueStandingController::class, 'update'])->name('standings.update');
    Route::delete('/standings/{standing}', [LeagueStandingController::class, 'destroy'])->name('standings.destroy');
});
