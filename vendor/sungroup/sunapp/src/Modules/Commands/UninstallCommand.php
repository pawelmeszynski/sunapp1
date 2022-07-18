<?php

namespace SunApp\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Json;
use SunApp\Modules\IO\ConsoleIO;
use SunApp\Modules\Process\Uninstaller;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UninstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:uninstall';

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
            $this->uninstallFromFile();

            return;
        }

        $this->uninstall(
            $this->argument('name'),
            $this->argument('version'),
            $this->option('type'),
            $this->option('tree')
        );
    }

    /**
     * Install modules from modules.json file.
     */
    protected function uninstallFromFile()
    {
        if (!file_exists($path = base_path('modules.json'))) {
            $this->error("File 'modules.json' does not exist in your project root.");

            return;
        }

        $modules = Json::make($path);

        $dependencies = $modules->get('require', []);

        foreach ($dependencies as $module) {
            $module = collect($module);

            $this->uninstall(
                $module->get('name'),
                $module->get('version'),
                $module->get('type')
            );
        }
    }

    /**
     * Install the specified module.
     *
     * @param string $name
     * @param string $version
     * @param string $type
     * @param bool $tree
     */
    protected function uninstall($name, $version = 'dev-master', $type = 'composer', $tree = false)
    {
        $output = new ConsoleIO($this->input, $this->output, new HelperSet(array()));
        $output->title('Uninstall <info>' . $name . '</info> module.');
        $installer = new Uninstaller(
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

        /*if (!$this->option('no-update')) {
            $this->call('module:update', [
                'module' => $installer->getModuleName(),
            ]);
        }*/

        $this->info("Module <info>" . $name . "</info> uninstall successfully.");
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
            ['timeout', null, InputOption::VALUE_OPTIONAL, 'The process timeout.', null],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The installation path.', null],
            ['type', null, InputOption::VALUE_OPTIONAL, 'The type of installation.', null],
            ['tree', null, InputOption::VALUE_NONE, 'Install the module as a git subtree', null],
            ['no-update', null, InputOption::VALUE_NONE, 'Disables the automatic update of the dependencies.', null],
        ];
    }
}
