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
    // Lig tablosu
    Route::get('/standings', [ChampionsLeagueApiController::class, 'getStandings'])->name('standings');
    
    // Maçlar
    Route::get('/matches', [ChampionsLeagueApiController::class, 'getAllMatches'])->name('matches.all');
    Route::get('/matches/week/{week}', [ChampionsLeagueApiController::class, 'getMatchesByWeek'])->name('matches.by-week');
    Route::get('/matches/grouped', [ChampionsLeagueApiController::class, 'getMatchesByWeekGrouped'])->name('matches.grouped');
    Route::get('/matches/{match}', [ChampionsLeagueApiController::class, 'getMatch'])->name('matches.show');
    
    // Takımlar
    Route::get('/teams', [ChampionsLeagueApiController::class, 'getTeams'])->name('teams');
    
    // Maç sıfırlama
    Route::post('/matches/reset', [ChampionsLeagueApiController::class, 'resetAllMatches'])->name('matches.reset');
}); 