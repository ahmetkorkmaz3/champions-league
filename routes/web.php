<?php

use App\Http\Controllers\ChampionsLeagueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Champions League Routes
Route::prefix('champions-league')->name('champions-league.')->group(function () {
    Route::get('/', [ChampionsLeagueController::class, 'index'])->name('index');
    Route::get('/predictions', [ChampionsLeagueController::class, 'predictions'])->name('predictions');

    Route::post('/play-week', [ChampionsLeagueController::class, 'playWeek'])->name('play-week');
    Route::post('/play-all', [ChampionsLeagueController::class, 'playAllMatches'])->name('play-all');

    Route::put('/matches/{match}/update', [ChampionsLeagueController::class, 'updateMatch'])->name('matches.update');
    Route::put('/matches/{match}/reset', [ChampionsLeagueController::class, 'resetMatch'])->name('matches.reset');
    Route::post('/reset', [ChampionsLeagueController::class, 'resetAllMatches'])->name('reset');
});
