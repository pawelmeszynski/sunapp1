<?php

namespace SunAppModules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SunAppModules\Core\Entities\SecurityLocks;
use SunAppModules\Core\Http\Controllers\SecurityLocksController;

class CheckIPLock
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
        if (env('SECURITY_ERROR_LOGGING', false)) {
            $ip = $request->ip();
            SecurityLocksController::checkIfLock($ip);
            SecurityLocksController::checkIfUnlock($ip);
            $locks = new SecurityLocks();
            if ($locks->isIPLocked($ip)) {
                abort(403, trans('core::messages.IP_is_locked'));
            }
        }
        return $next($request);
    }
}
