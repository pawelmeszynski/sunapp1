<?php

namespace SunAppModules\SunBet\Providers;

use SunAppModules\Core\Providers\ModuleServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;

class SunBetServiceProvider extends ModuleServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router, Kernel $kernel)
    {
        parent::boot($router, $kernel);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
      * Get the services provided by the provider.
      *
      * @return array
      */
    public function provides()
    {
        return [];
    }
}
