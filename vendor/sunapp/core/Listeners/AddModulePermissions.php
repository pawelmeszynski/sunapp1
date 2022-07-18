<?php

namespace SunAppModules\Core\Listeners;

use Bouncer;
use SunAppModules\Core\Events\RegisterPermissions;

class AddModulePermissions
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param RegisterPermissions $event
     */
    public function handle(RegisterPermissions $event)
    {
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::users.view',
            'title' => 'core::module.users',
        ]);
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::users.create',
            'title' => 'core::module.users',
        ]);
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::users.edit',
            'title' => 'core::module.users',
        ]);
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::users.destroy',
            'title' => 'core::module.users',
        ]);

        Bouncer::ability()->firstOrCreate([
            'name' => 'core::roles.view',
            'title' => 'core::module.roles',
        ]);
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::roles.create',
            'title' => 'core::module.roles',
        ]);
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::roles.edit',
            'title' => 'core::module.roles',
        ]);
        Bouncer::ability()->firstOrCreate([
            'name' => 'core::roles.destroy',
            'title' => 'core::module.roles',
        ]);
    }
}
