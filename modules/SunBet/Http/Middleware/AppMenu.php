<?php

namespace SunAppModules\SunBet\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        return $next($request);
    }
}
