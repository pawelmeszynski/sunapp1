<?php

namespace SunAppModules\Core\src\Bouncer;

use Silber\Bouncer\Bouncer as BaseBouncer;

class Bouncer extends BaseBouncer
{
    /**
     * The bouncer guard instance.
     *
     * @var \Silber\Bouncer\Guard
     */
    protected $guard;

    /**
     * The access gate instance.
     *
     * @var \Illuminate\Contracts\Auth\Access\Gate|null
     */
    protected $gate;

    /**
     * Determine if the given ability is allowed.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function can($ability, $arguments = [])
    {
        if (request()->ajax() && $ability == 'show' && !auth()->guest()) {
            return true;
        }
        return $this->gate()->allows($ability, $arguments);
    }
}
