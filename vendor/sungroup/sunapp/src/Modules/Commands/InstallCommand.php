<?php

namespace SunApp\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Json;
use SunApp\Modules\IO\ConsoleIO;
use SunApp\Modules\Process\Installer;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the specified module by given package name (vendor/name).';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (is_null($this->argument('name'))) {
            if (!$this->option('all')) {
                $this->installFromFile();
            } else {
                $modules = \Module::all();
                uasort($modules, function ($a, $b) {
                    if ($a->get('order') === $b->get('order')) {
                        return 0;
                    }
                    return $a->get('order') > $b->get('order') ? 1 : -1;
                });
                foreach ($modules as $module) {
                    $this->installFromFile($module->getPath() . DIRECTORY_SEPARATOR . 'module.json');
                }
                if ($this->option('enable')) {
                    foreach ($modules as $module) {
                        Artisan::call("module:enable {$module->getName()}");
                    }
                }
            }
            Artisan::call("cache:clear");
            Artisan::call("view:clear");
            $this->info('Application cache cleared!');
            return;
        }

        $this->install(
            $this->argument('name'),
            $this->argument('version'),
            $this->option('type'),
            $this->option('tree')
        );
        if ($this->option('enable')) {
            Artisan::call("module:enable {$this->argument('name')}");
        }
        Artisan::call("cache:clear");
        Artisan::call("view:clear");
        $this->info('Application cache cleared!');
    }

    /**
     * Install modules from modules.json file.
     */
    protected function installFromFile($path = false)
    {
        if (!$path) {
            $path = base_path('module.json');
        }
        if (!file_exists($path)) {
            $this->error("File 'module.json' does not exist in your project root.");

            return;
        }

        $module = Json::make($path);
        $this->install(
            $module->get('name'),
            $module->get('version'),
            $module->get('type')
        );
    }

    /**
     * Install the specified module.
     *
     * @param string $name
     * @param string $version
     * @param string $type
     * @param bool $tree
     */
    protected function install($name, $version = 'dev-master', $type = 'composer', $tree = false)
    {
        $output = new ConsoleIO($this->input, $this->output, new HelperSet(array()));
        $output->title('Install <info>' . $name . '</info> module.');
        $installer = new Installer(
            $name,
            $version,
            $type ?: $this->option('type'),
            $tree ?: $this->option('tree')
        );

        $installer->setRepository($this->laravel['modules']);

        $installer->setConsole($this);
        $installer->setOutput($output);

        if ($timeout = $this->option('timeout')) {
            $installer->setTimeout($timeout);
        }

        if ($path = $this->option('path')) {
            $installer->setPath($path);
        }

        $installer->run();

        $this->info("Module <info>" . $name . "</info> install successfully.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of module will be installed.'],
            ['version', InputArgument::OPTIONAL, 'The version of module will be installed.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['all', null, InputOption::VALUE_NONE, 'Install all modules.', null],
            ['force', null, InputOption::VALUE_NONE, 'Force install modules.', null],
            ['timeout', null, InputOption::VALUE_OPTIONAL, 'The process timeout.', null],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The installation path.', null],
            ['type', null, InputOption::VALUE_OPTIONAL, 'The type of installation.', null],
            ['tree', null, InputOption::VALUE_NONE, 'Install the module as a git subtree', null],
            ['no-update', null, InputOption::VALUE_NONE, 'Disables the automatic update of the dependencies.', null],
            ['enable', null, InputOption::VALUE_NONE, 'Automatic enable module.', null]
        ];
    }
}
