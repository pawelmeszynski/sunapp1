<?php

namespace SunAppModules\Core\src\View;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the view environment.
     */
    public function register()
    {
        $finder = new FileViewFinder($this->app['files'], $this->app['config']['view.paths']);
        $hints = app('view')->getFinder()->getHints();
        foreach ($hints as $namespace => $hint) {
            $finder->replaceNamespace($namespace, $hint);
        }
        $this->app->singleton('view', function ($app) use ($finder) {
            $resolver = $app['view.engine.resolver'];
            $env = new Factory($resolver, $finder, $app['events']);
            $env->setContainer($app);
            $env->share('app', $app);
            return $env;
        });
    }
}
