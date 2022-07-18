<?php

namespace SunApp\Providers;

use Illuminate\Support\ServiceProvider;
use SunApp\Modules\Commands\ContentCommand;
use SunApp\Modules\Commands\InstallCommand;
use SunApp\Modules\Commands\MigrateCommand;
use SunApp\Modules\Commands\UninstallCommand;
use SunApp\Modules\Commands\UpdateCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        InstallCommand::class,
        UninstallCommand::class,
        UpdateCommand::class,
        ContentCommand::class,
        MigrateCommand::class
    ];

    /**
     * Register the commands.
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * @return array
     */
    public function provides()
    {
        $provides = $this->commands;
        return $provides;
    }
}
