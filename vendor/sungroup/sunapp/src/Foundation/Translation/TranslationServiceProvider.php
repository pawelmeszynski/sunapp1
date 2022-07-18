<?php

namespace SunApp\Foundation\Translation;

use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('translation.loader', function ($command, $app) {
            return new FileLoader($app['files'], [core_path('resources/lang'), $app['path.lang']]);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['translation.loader'];
    }
}
