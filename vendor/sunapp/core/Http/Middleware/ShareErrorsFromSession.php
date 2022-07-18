<?php

namespace SunAppModules\Core\Http\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

class ShareErrorsFromSession
{
    /**
     * The view factory implementation.
     *
     * @var Factory
     */
    protected $theme = false;

    /**
     * Create a new error binder instance.
     *
     * @param  Factory  $view
     */
    public function __construct()
    {
        if (app()->bound('theme')) {
            $this->theme = app('theme');
        }
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
        // If the current session has an "errors" variable bound to it, we will share
        // its value with all view instances so the views can easily access errors
        // without having to bind. An empty bag is set when there aren't errors.
        if ($this->theme) {
            $this->theme->share(
                'errors',
                $request->session()->get('errors') ?: new ViewErrorBag()
            );
        }
        // Putting the errors in the view for every view allows the developer to just
        // assume that some errors are always available, which is convenient since
        // they don't have to continually run checks for the presence of errors.

        return $next($request);
    }
}
