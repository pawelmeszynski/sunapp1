<?php

namespace SunAppModules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SunAppModules\Core\Entities\Access;
use SunAppModules\Core\Entities\Config;

class CheckIPAccess
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
        $domain = $request->getHost();
        $ip = $request->ip();
        $excludeDomains = config('access.exclude_domains');
        $excludeIps = config('access.exclude_ips');

        if (ends_with($domain, $excludeDomains) || in_array($ip, $excludeIps)) {
            return $next($request);
        }

        if (Config::where('key', 'public_access')->where('value', 1)->first()) {
            return $next($request);
        } else {
            $accesses = Access::all();
            foreach ($accesses as $item) {
                if (ip_in_range($ip, $item->ip_address_mask)) {
                    return $next($request);
                }
            }
        }
        abort(403, trans('core::access.no_access'));
    }
}
