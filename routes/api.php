<?php

use App\Http\Controllers\Api\ChampionsLeagueApiController;
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
    // Teams
    Route::get('/teams', [ChampionsLeagueApiController::class, 'teams'])->name('teams');
    
    // Matches
    Route::get('/matches', [ChampionsLeagueApiController::class, 'matches'])->name('matches.index');
    Route::get('/matches/by-week', [ChampionsLeagueApiController::class, 'matchesByWeek'])->name('matches.by-week');
    Route::get('/matches/{match}', [ChampionsLeagueApiController::class, 'showMatch'])->name('matches.show');
    
    // Standings
    Route::get('/standings', [ChampionsLeagueApiController::class, 'standings'])->name('standings');
    
    // Actions
    Route::post('/matches/reset', [ChampionsLeagueApiController::class, 'resetMatches'])->name('matches.reset');
    Route::post('/matches/play-all', [ChampionsLeagueApiController::class, 'playAllMatches'])->name('matches.play-all');
    Route::post('/matches/play-week', [ChampionsLeagueApiController::class, 'playWeek'])->name('matches.play-week');
}); 