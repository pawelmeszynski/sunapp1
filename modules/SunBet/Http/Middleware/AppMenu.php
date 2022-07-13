<?php

namespace SunAppModules\SunBet\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Menu;

class AppMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Menu::make('AppSideBar', function ($menu) {
            $home = $menu->add(
                'SunBet',
                ['action' => '\SunAppModules\SunBet\Http\Controllers\SunBetController@index']
            )->data(['icon' => 'feather icon-user']);
            if ($home) {
                $home->add(
                    'Tabela userów',
                    ['action' => '\SunAppModules\SunBet\Http\Controllers\UsersController@index']
                )->data(['icon' => 'feather icon-user']);
                $home->add(
                    'Turnieje',
                    ['action' => '\SunAppModules\SunBet\Http\Controllers\CompetitionsController@index']
                )->data(['icon' => 'feather icon-user']);
                $home->add(
                    'Zlicz punkty',
                    ['route' => 'SunApp::sunbet.points.calculate']
                )->data(['icon' => 'feather icon-user']);
                $home->add(
                    'Pobierz wyniki',
                    ['route' => 'SunApp::sunbet.matches.fetch']
                )->data(['icon' => 'feather icon-user']);
            }
        }
        );
        return $next($request);

    }
}
