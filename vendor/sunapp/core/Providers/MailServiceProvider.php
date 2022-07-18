<?php

namespace SunAppModules\Core\Providers;

use Swift_Mailer;
use SunAppModules\Core\Mail\SunmailManager;
use Illuminate\Mail\MailServiceProvider as BaseMailServiceProvider;

class MailServiceProvider extends BaseMailServiceProvider
{
    public function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new SunmailManager($app);
        });
    }
}
