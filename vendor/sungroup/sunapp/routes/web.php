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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/modules', 'ModulesController@index')->name('SunAppModules::index');

Route::get('/modules/install', 'ModulesController@install')->name('SunAppModules::install');
Route::get('/modules/update', 'ModulesController@update')->name('SunAppModules::update');
Route::get('/modules/uninstall', 'ModulesController@uninstall')->name('SunAppModules::uninstall');

Route::get('/modules/enable', 'ModulesController@enable')->name('SunAppModules::enable');
Route::get('/modules/disable', 'ModulesController@disable')->name('SunAppModules::disable');
