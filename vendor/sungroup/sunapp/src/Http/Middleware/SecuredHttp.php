<?php

namespace SunApp\Http\Middleware;

use Closure;

class SecuredHttp
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
        if (!$request->secure()) {
            return redirect()->secure($request->path());
        }
        return $next($request);
    }
}
