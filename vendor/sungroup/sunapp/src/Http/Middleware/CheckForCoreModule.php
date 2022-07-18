<?php

namespace SunApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Module;
use Route;

class CheckForCoreModule
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = Route::getRoutes()->match($request);
        $route_name = $route->getName();
        if ((!Str::start($route_name, 'SunAppModules::') || $route_name === null) && !Module::has('Core')) {
            $installed_modules = app('installed_modules');
            $installed_modules = $installed_modules->where('alias', 'core')->all();
            foreach ($installed_modules as $installed_module) {
                $installed_module->delete();
            }
            return redirect()->route(
                'SunAppModules::install',
                ['package' => Crypt::encryptString('sunapp/core'), 'url' => $request->getUri()]
            );
        } else {
            if ((!Str::start($route_name, 'SunAppModules::') || $route_name === null) && !Module::enabled('Core')) {
                return redirect()->route(
                    'SunAppModules::enable',
                    ['module' => Crypt::encryptString('Core'), 'url' => $request->getUri()]
                );
            }
        }
        return $next($request);
    }
}
