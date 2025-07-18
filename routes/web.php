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
});
