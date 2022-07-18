<?php

namespace SunApp\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Json;
use SunApp\Modules\IO\ConsoleIO;
use SunApp\Modules\Process\Updater;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the specified module by given package name (vendor/name).';

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
            $this->updateFromFile();

            return;
        }

        $this->update(
            $this->argument('name'),
            $this->argument('version'),
            $this->option('type'),
            $this->option('tree')
        );
    }

    /**
     * Install modules from modules.json file.
     */
    protected function updateFromFile()
    {
        if (!file_exists($path = base_path('modules.json'))) {
            $this->error("File 'modules.json' does not exist in your project root.");

            return;
        }

        $modules = Json::make($path);

        $dependencies = $modules->get('require', []);

        foreach ($dependencies as $module) {
            $module = collect($module);

            $this->update(
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
    protected function update($name, $version = 'dev-master', $type = 'composer', $tree = false)
    {
        $output = new ConsoleIO($this->input, $this->output, new HelperSet(array()));
        $output->title('Update <info>' . $name . '</info> module.');
        $installer = new Updater(
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

        $this->info("Module <info>" . $name . "</info> update successfully.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of module will be updated.'],
            ['version', InputArgument::OPTIONAL, 'The version of module will be updated.'],
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
            ['path', null, InputOption::VALUE_OPTIONAL, 'The update path.', null],
            ['type', null, InputOption::VALUE_OPTIONAL, 'The type of update.', null],
            ['tree', null, InputOption::VALUE_NONE, 'Update the module as a git subtree', null],
            ['no-update', null, InputOption::VALUE_NONE, 'Disables the automatic update of the dependencies.', null],
        ];
    }
}
