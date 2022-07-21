<?php

namespace SunAppModules\Core\Http\Middleware;

use Bouncer;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Menu;
use SunAppModules\Core\Entities\ExtraField;
use SunAppModules\Core\Entities\Log;
use SunAppModules\Core\Entities\Audit;
use SunAppModules\Core\Entities\Role;
use SunAppModules\Core\Entities\SecurityExceptions;
use SunAppModules\Core\Entities\SecurityLocks;
use SunAppModules\Core\Entities\User;
use SunAppModules\Core\Entities\UserGroup;
use SunAppModules\Core\Entities\Access;

class AppMenu
{
    /**
     * The authentication factory instance.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  Auth  $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $Users = Bouncer::can('show', User::class);
        $UsersGroups = Bouncer::can('show', UserGroup::class);
        $Roles = Bouncer::can('show', Role::class);
        $ExtraFields = Bouncer::can('show', ExtraField::class);
        $Logs = Bouncer::can('show', Log::class);
        $Access = Bouncer::can('show', Access::class);
        $Audits = Bouncer::can('show', Audit::class);

        $SecurityLocks = Bouncer::can('show', SecurityLocks::class);
        $SecurityExceptions = Bouncer::can('show', SecurityExceptions::class);
        Menu::make('AppSideBar', function ($menu) use (
            $Users,
            $UsersGroups,
            $Roles,
            $ExtraFields,
            $Logs,
            $SecurityLocks,
            $SecurityExceptions,
            $Access,
            $Audits
        ) {
            $home = $menu->add(
                trans('core::module.dashboard'),
                [
                    'action' => '\SunAppModules\Core\Http\Controllers\DashboardController@index'
                ]
            )->data(['icon' => 'feather icon-home'])->data('order', 0);
            if ($Users || $UsersGroups || $Roles) {
                $users = $menu->add(trans('core::module.accounts'), [
                    'disableActivationByURL' => true
                ])->data(['icon' => 'feather icon-users'])->data('order', 90);
                if ($Users) {
                    $users->add(
                        trans('core::module.users'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\UsersController@index']
                    )->data(['icon' => 'feather icon-user']);
                }
                if ($UsersGroups) {
                    $users->add(
                        trans('core::module.users_groups'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\UserGroupsController@index']
                    )->data(['icon' => 'feather icon-users']);
                }
                if ($Roles) {
                    $users->add(
                        trans('core::module.users_roles'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\RolesController@index']
                    )->data(['icon' => 'feather icon-user-check']);
                }
            }

            if ($ExtraFields || $SecurityLocks || $SecurityExceptions || $Access || $Audits) {
                $settings = $menu->add(trans('core::module.settings'), [
                    'disableActivationByURL' => true
                ])->data(['icon' => 'feather icon-settings'])->nickname('settings')->data('order', 100);

                $settings->add(
                    trans('core::module.modules'),
                    ['action' => '\SunAppModules\Core\Http\Controllers\ModulesController@index']
                )->data(['icon' => 'feather icon-box', 'order' => 1]);
                if ($ExtraFields) {
                    $settings->add(
                        trans('core::module.extra_fields'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\ExtraFieldsController@index']
                    )->data(['icon' => 'feather icon-file-plus', 'order' => 2]);
                }
                if ($Logs) {
                    $settings->add(
                        trans('core::module.logs'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\LogController@index']
                    )->data(['icon' => 'feather icon-file-text', 'order' => 3]);
                }
                if ($Audits) {
                    $settings->add(
                        trans('core::module.update_history'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\AuditsController@index']
                    )->data(['icon' => 'feather icon-file-text', 'order' => 4]);
                }
                if ($Access) {
                    $settings->add(
                        trans('core::module.accesses'),
                        ['action' => '\SunAppModules\Core\Http\Controllers\AccessController@index']
                    )->data(['icon' => 'feather icon-file-plus', 'order' => 4]);
                }
                if ($SecurityLocks || $SecurityExceptions) {
                    $security = $settings->add(trans('core::module.security'), [
                        'disableActivationByURL' => true
                    ])->data(['icon' => 'feather icon-lock'])->nickname('settings.security')->data('order', 100);
                    if ($SecurityLocks) {
                        $security->add(
                            trans('core::module.security_locks'),
                            ['action' => '\SunAppModules\Core\Http\Controllers\SecurityLocksController@index']
                        )->data(['icon' => 'feather icon-slash', 'order' => 5]);
                    }
                    if ($SecurityExceptions) {
                        $security->add(
                            trans('core::module.security_exceptions'),
                            ['action' => '\SunAppModules\Core\Http\Controllers\SecurityExceptionsController@index']
                        )->data(['icon' => 'feather icon-file-plus', 'order' => 6]);
                    }
                }
            }

            if (\Auth::user() && \Auth::user()->superadmin) {
                $settings->add(
                    trans('core::actions.cache_clear'),
                    ['route' => 'SunApp::core.cache.clear']
                )->data(['icon' => 'feather icon-trash-2', 'order' => 5]);

                $settings->add(
                    trans('core::actions.minify_assets'),
                    ['route' => 'SunApp::core.assets.minify']
                )->data(['icon' => 'feather icon-refresh-cw', 'order' => 6]);
            }
        });
        Menu::make('AppNavBarBookmarks', function ($menu) {
        });
        Menu::make('AppNavBar', function ($menu) {
        });
        if ($this->auth->user() && config('system.app') == 1) {
            Menu::make('AppNavBar', function ($menu) {
                $user = $menu->add('')->data('order', 0);
                $user->nickname('user');
                $user->link->attr(['class' => 'dropdown-user-link']);
                $user->after(view('core::user.info', ['user' => $this->auth->user()]));
                // TODO: należy dodac obsługę konta
                //$user->add('link1')->data(['icon'=>'feather icon-layout'])->divide();
                /*$change_2fa = $user->add('Google 2fa', ['route' => 'SunApp::user.2fa'])
                    ->data(['icon' => 'feather icon-archive']);
                $change_2fa->link->attr([
                    'onclick' => 'event.preventDefault(); window.location.href = this.getAttribute("href");'
                ]);*/

                $account = $user->add(trans('core::users.my-account'), [
                    'route' => ['SunApp::core.my-account.edit', 'my_account' => $this->auth->user()->id]
                ])->data(['icon' => 'feather icon-layout']);
                /*$account->link->attr([
                    'onclick' => 'event.preventDefault(); window.location.href = this.getAttribute("href");'
                ]);*/

                $logout = $user->add(trans('core::users.logout'), ['route' => 'SunApp::logout'])
                    ->data(['icon' => 'feather icon-power']);
                $logout->link->attr([
                    'onclick' => 'event.preventDefault(); document.getElementById(\'logout-form\').submit();'
                ]);
            });
        }
        return $next($request);
    }
}
