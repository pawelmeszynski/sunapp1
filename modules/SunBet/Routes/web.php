<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use SunAppModules\SunBet\Entities\SunbetUser;

Route::prefix('sunbet')->name('.')->group(function () {
    Route::resource('/', 'SunBetController');
    Route::resource('/users', 'UsersController');
    Route::resource('/competitions', 'CompetitionsController')->except('create');
    Route::get('/calculate-points', function () {
        Artisan::call('points:calculate');
        return back();
    })->name('points.calculate');
    Route::get('/fetch-matches', function () {
        Artisan::call('matches:fetch');
        return back();
    })->name('matches.fetch');
});

Route::get('/fetch-areas', function () {
    dump(Artisan::call('areas:fetch'));
});
Route::get('/fetch-competitions', function () {
    dump(Artisan::call('competitions:fetch'));
});
Route::get('/fetch-data', function () {
    dump(Artisan::call('data:fetch'));
});
Route::get('/fetch-teams', function () {
    dump(Artisan::call('teams:fetch'));
});
Route::get('login/github', 'UsersController@redirectToProvider');
Route::get('login/github/callback', 'UsersController@handleProviderCallback');

