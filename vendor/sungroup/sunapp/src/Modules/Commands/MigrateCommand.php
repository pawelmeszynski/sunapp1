<?php

namespace SunApp\Modules\Commands;

use Nwidart\Modules\Commands\MigrateCommand as Command;

class MigrateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the migrations from the specified module or from all modules.';

    /**
     * @var \Nwidart\Modules\Contracts\RepositoryInterface
     */
    protected $module;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = $this->laravel['modules'];

        $name = $this->argument('module');

        if ($name) {
            $module = $this->module->findOrFail($name);

            return $this->migrate($module);
        }
        if ($this->option('force')) {
            \Config::set('ignore_db_status', true);
        }
        foreach ($this->module->getOrdered($this->option('direction')) as $module) {
            $this->line('Running for module: <info>' . $module->getName() . '</info>');

            $this->migrate($module);
        }
    }
}
