<?php

namespace SunAppModules\Core\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use SunApp\Http\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  Request  $request
     * @param  array  $guards
     *
     * @throws AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request)
        );
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return route('SunApp::login');
        }
        abort(401, 'Unauthorized');
    }
}
