<?php

namespace SunAppModules\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;
use SunAppModules\Core\Entities\Access;
use SunAppModules\Core\src\Google2FALaravel\Support\Authenticator;
use User;

class GoogleAuthenticate
{
    public function handle($request, Closure $next)
    {
        $domain = $request->getHost();
        $ip = $request->ip();
        $excludeDomains = config('access.exclude_domains');
        $excludeIps = config('access.exclude_ips');

        if (
            ends_with($domain, $excludeDomains) || in_array($ip, $excludeIps) || ($request->cookie('device_token')
                && $request->cookie('device_token') == $request->user()->remember_device_token)
        ) {
            return $next($request);
        }

        $accesses = Access::where('w_2fa', 1)->get();
        foreach ($accesses as $item) {
            if (ip_in_range($ip, $item->ip_address_mask)) {
                return $next($request);
            }
        }

        $authenticator = app(Authenticator::class)->boot($request);

        if ($authenticator->isAuthenticated()) {
            if ($request->session()->pull('remember_device')) {
                do {
                    $token = str_random(100);
                    $user = User::where('remember_device_token', $token)->count();
                } while ($user != 0);
                $userEmail = $request->user()->email;
                $user = User::where('email', $userEmail)->update(['remember_device_token' => $token]);
                Cookie::queue(Cookie::make('device_token', $token, 43200));
            }
            return $next($request);
        }
        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
