<?php

namespace SunAppModules\Core\Mail;

use Illuminate\Support\Manager;
use Illuminate\Mail\TransportManager;

class SunmailManager extends TransportManager
{
    protected function createSunmailDriver()
    {
        $config = app()['config']->get('mail', []);

        return new SunmailTransport(
            $this->guzzle($config),
            $config['host']
        );
    }
}
