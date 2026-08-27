<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RaceController;
use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::view('/', 'welcome');

Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/races/{race}', [RaceController::class, 'show'])->name('races.show');
Route::get('/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/leagues', [LeagueController::class, 'index'])->name('leagues.index');
    Route::post('/leagues', [LeagueController::class, 'store'])->name('leagues.store');
    Route::post('/leagues/join', [LeagueController::class, 'join'])->name('leagues.join');
    Route::get('/leagues/{league}', [LeagueController::class, 'show'])->name('leagues.show');
    Route::post('/leagues/{league}/start-auction', [LeagueController::class, 'startAuction'])->name('leagues.start-auction');
    Route::post('/leagues/{league}/close-round', [LeagueController::class, 'closeRound'])->name('leagues.close-round');
    Route::delete('/leagues/{league}', [LeagueController::class, 'destroy'])->name('leagues.destroy');
});

require __DIR__.'/auth.php';
