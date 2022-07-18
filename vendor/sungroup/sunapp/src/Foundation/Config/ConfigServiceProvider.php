<?php

namespace SunApp\Foundation\Config;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['config'] = new Repository($this->app['config']->all());
        $this->app['config']->setPath(config_path());
        $this->app['config']->setCachePath($this->app->getCachedConfigPath());
        $this->app['config']->setDispatcher($this->app['events']);

        $this->app->bind('app_config', function () {
            return $this->app['config'];
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['config'];
    }
}
