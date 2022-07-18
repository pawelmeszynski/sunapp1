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

Auth::routes(['verify' => true, 'register' => config('system.user_register')]);
Route::get('/', 'HomeController@index')->name('home');
Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
Route::get('i18next/fetch/{lang}/{namespace?}', 'I18NextController@fetch');
Route::get('/logout', function () {
    abort(404);
});

Route::post('/register_step1', 'Auth\RegisterController@stepOneRegistration')->name('register_step1');
Route::get('/complete-registration', 'Auth\RegisterController@completeRegistration')->name('complete_registration');

Route::post('/2fa', function () {
    Auth::user()->update([
        'verified_at_2fa_google' => date('Y-m-d H:i:s'),
    ]);
    return redirect(URL()->previous());
})->name('2fa')->middleware('2fa');

Route::middleware(['auth', 'verified', '2fa'])->name('core.')->group(function () {
    Route::get('/roles/abilities', 'RolesController@showReqiredAbilities');
    Route::resource('roles', 'RolesController');
    Route::resource('users', 'UsersController');
    Route::post('users/{id}/super', 'UsersController@super')->name('users.super');
    Route::post('users/{id}/ban', 'UsersController@ban')->name('users.ban');
    Route::any('users/login_as/{to_id}', 'UsersController@loginAs')->name('users.login_as');
    Route::post('users/{id}/2fa/enable', 'UsersController@enable2fa')->name('users.enable2fa');
    Route::post('users/{id}/2fa/reset2fa', 'UsersController@reset2fa')->name('users.reset2fa');
    Route::resource('groups', 'UserGroupsController');
    Route::resource('my-account', 'MyAccountController')->only([
        'edit', 'update'
    ]);

    Route::resource('sec-exceptions', 'SecurityExceptionsController');
    Route::resource('locks', 'SecurityLocksController');
    /*->name('locks.index');
    Route::any('security_locks/{lock}', 'SecurityLocksController@index')->name('locks.show');*/

    Route::resource('logs', 'LogController')->only([
        'index', 'show'
    ]);

    Route::resource('extra-fields', 'ExtraFieldsController');

    Route::get('cacheClear', 'CacheController@cacheClear')->name('cache.clear');
    Route::get('minify-assets', 'AssetsController@minifyBackendAssets')->name('assets.minify');
    Route::resource('modules', 'ModulesController');
    Route::post('modules/enable', 'ModulesController@enable')->name('modules.enable');
    Route::get('getModules/getFromComposer', 'ModulesController@getModulesFromComposer')
    ->name('modules.getFromComposer');
    Route::post('getModules/install', 'ModulesController@installModule')
    ->name('modules.install');

    Route::resource('access', 'AccessController');
    Route::get('access-enable', 'AccessController@enableDisable')->name('access-enable');
    Route::get('/get/version-data', 'HomeController@getVersionData')->name('get.version-data');
});

Route::any(
    'login_as/{to_id}/{from_id}/{token}',
    '\SunAppModules\Core\Http\Controllers\Auth\LoginController@loginAs'
)->name('login_as');

Route::get('update-history', 'AuditsController@index');
Route::get('update-history/element', 'AuditsController@element');

