<?php

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
Route::get('/', 'ApiController@info')->name('info');
Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::get('/user', 'ApiController@loggedUser')->name('user');
});
