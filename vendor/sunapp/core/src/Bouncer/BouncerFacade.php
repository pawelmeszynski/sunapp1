<?php

namespace SunAppModules\Core\src\Bouncer;

use Illuminate\Support\Facades\Facade;

class BouncerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Bouncer::class;
    }
}
