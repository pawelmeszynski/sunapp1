<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Route::post('register', 'Api\UserController@register');
//Route::post('login', 'Api\UserController@login');
Route::middleware('auth:api')->group(function () {

    Route::apiresource('matches', 'Api\MatchesController');

    Route::post('/matches/predict', 'Api\MatchesController@store');

    Route::get('/predicts', 'Api\MatchesController@predicts');

    Route::get('/predicts/{id}', 'Api\MatchesController@showPredict');

    Route::get('/standings', 'Api\StandingsController@index');

    Route::get('/standings/{id}', 'Api\StandingsController@show');

    Route::get('/teams/{id}', 'Api\TeamsController@show');

    Route::get('/userstandings', 'Api\UserStandingsController@index');

    Route::get('/userstandings/{id}', 'Api\UserStandingsController@show');

    Route::get('/teams', 'Api\TeamsController@index');

    Route::get('user', function (Request $request) {
        return $request->user();
    });
});

//Route::get('user', function (Request $request) {
//    return $request->user();
//});

//Route::post('signup', 'Api\UserController@signup');
//Route::post('login', 'Api\UserController@login');
