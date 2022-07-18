<?php

namespace SunApp\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Json;
use SunApp\Modules\IO\ConsoleIO;
use SunApp\Modules\Process\Store;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ContentCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Added test content to specified modules';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (is_null($this->argument('name'))) {
            if (!$this->option('all')) {
                $this->storeFromFile();
            } else {
                $modules = Module::all();
                uasort($modules, function ($a, $b) {
                    if ($a->get('order') === $b->get('order')) {
                        return 0;
                    }
                    return $a->get('order') > $b->get('order') ? 1 : -1;
                });
                foreach ($modules as $module) {
                    $name = lcfirst(join(array_map('lcfirst', explode('-', $module->getAlias()))));
                    if (app('events')->hasListeners('modules.' . $name . '.content')) {
                        $this->storeFromFile($module->getPath() . DIRECTORY_SEPARATOR . 'module.json');
                    }
                }
            }
            return 0;
        }

        $this->store(
            $this->argument('name'),
            $this->option('type')
        );
        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::OPTIONAL,
                'Please input names of module or type "all" to add content in all modules'
            ],
        ];
    }

    protected function getOptions()
    {
        return [
            ['type', null, InputOption::VALUE_OPTIONAL, 'The type of update.', null],
            ['timeout', null, InputOption::VALUE_OPTIONAL, 'The process timeout.', null],
            ['all', null, InputOption::VALUE_NONE, 'Add content to all modules.', null],
        ];
    }

    private function storeFromFile($path = false)
    {
        if (!$path) {
            $path = base_path('module.json');
        }

        if (!file_exists($path)) {
            $this->error("File 'modules.json' does not exist in your project root.");

            return;
        }

        $module = Json::make($path);

        $this->store(
            $module->get('name'),
            $module->get('type')
        );
    }

    private function store($name, $type = 'composer')
    {
        $output = new ConsoleIO($this->input, $this->output, new HelperSet((array())));

        $output->title('Store content in <info>' . $name . '</info> module');

        $store = new Store(
            $name,
            $type ?: $this->option('type')
        );

        $store->setRepository($this->laravel['modules']);
        $store->setConsole($this);
        $store->setOutput($output);

        if ($timeout = $this->option('timeout')) {
            $store->setTimeout($timeout);
        }

        $store->run();

        $this->info("Module <info>" . $name . "</info> store successfully");
    }
}
