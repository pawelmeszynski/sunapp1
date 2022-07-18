<?php

namespace SunApp\Http\Middleware;

use Closure;

class RedirectIfNoSetup
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $route = \Route::getRoutes()->match($request);
        if (
            (
                !file_exists(storage_path('installed'))
                || !file_exists(app()->environmentFilePath())
                || app()->make('config')->get('app.key') == null
            ) && (
                !str_start($route->getName(), 'LaravelInstaller::')
                || $route->getName() === null
            )
        ) {
            return redirect(route('LaravelInstaller::welcome'));
        }
        return $next($request);
    }
}
