<?php

namespace SunAppModules\Core\src\Theme\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleWidgetGeneratorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:module-widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate widget structure.';

    /**
     * Widget view template global.
     *
     * @var bool
     */
    protected $global = false;

    /**
     * Repository config.
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Filesystem
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  Repository  $config
     * @param  File  $files
     * @return \Facuz\Theme\Commands\WidgetGeneratorCommand
     */
    public function __construct(Repository $config, File $files)
    {
        $this->config = $config;

        $this->files = $files;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Widget class name is camel case.
        $widgetClassName = ucfirst($this->getWidgetName());

        // Widget class file is camel with php extension.
        $widgetClassFile = $widgetClassName . '.php';

        // CamelCase for template.
        $widgetClassTpl = lcfirst($this->getWidgetName());

        // Get class template.
        $widgetClassTemplate = $this->getTemplate('widgetClass');

        // Default create not on a global.
        $watch = 'false';

        // If not specific a theme, not a global also return an error.
        if (!$this->argument('module')) {
            return $this->error('Please specific a module name');
        }

        $module = Module::find($this->getModule());
        if (!$module) {
            return $this->error('The theme "' . $this->getModule() . '" does not exist.');
        }
        $widgetClassTplWithModule = Str::kebab($this->getModule()) . '::' . $widgetClassTpl;
        $widgetNamespace = $this->config->get('theme.namespaces.module_widget.' . Str::kebab($this->getModule()));

        // Prepare class template.
        $widgetClassTemplate = preg_replace(
            ['|\{widgetNamespace\}|', '|\{widgetClass\}|', '|\{widgetTemplate\}|', '|\{watch\}|'],
            [$widgetNamespace, $widgetClassName, $widgetClassTplWithModule, $watch],
            $widgetClassTemplate
        );

        // Create widget directory.
        if (!$this->files->isDirectory($module->getPath() . '/Widgets')) {
            $this->files->makeDirectory($module->getPath() . '/Widgets', 0777, true);
        }

        // Widget class already exists.
        if ($this->files->exists($module->getPath() . '/Widgets/' . $widgetClassFile)) {
            return $this->error('Widget "' . $this->getWidgetName() . '" is already exists.');
        }

        // Create class file.
        $this->files->put($module->getPath() . '/Widgets/' . $widgetClassFile, $widgetClassTemplate);

        // Make file example.
        $this->makeFile('widgets/' . $widgetClassTpl . '.blade.php', $module, $this->getTemplate('widget.blade'));

        $this->info('Widget "' . $this->getWidgetName() . '" has been created.');
    }

    /**
     * Make file.
     *
     * @param  string  $file
     * @param  string  $template
     */
    protected function makeFile($file, $module, $template = null)
    {
        $dirname = dirname($module->getPath() . '/Resources/' . $file);
        // Checking directory.
        if (!$this->files->isDirectory($dirname)) {
            $this->files->makeDirectory($dirname, 0777, true);
        }

        if (!$this->files->exists($module->getPath() . '/Resources/' . $file)) {
            $this->files->put($module->getPath() . '/Resources/' . $file, $template);
        }
    }

    /**
     * Get the widget name.
     *
     * @return string
     */
    protected function getWidgetName()
    {
        // The first character must be lower.
        return ucfirst($this->argument('name'));
    }

    /**
     * Get the theme name.
     *
     * @return string
     */
    protected function getModule()
    {
        return $this->argument('module');
//        return strtolower($this->argument('theme'));
    }

    /**
     * Get the theme name.
     *
     * @return string
     */
    protected function getTheme()
    {
        return $this->argument('theme');
//        return strtolower($this->argument('theme'));
    }

    /**
     * Get default template.
     *
     * @param  string  $template
     * @return string
     */
    protected function getTemplate($template)
    {
        $path = realpath(__DIR__ . '/../templates/' . $template . '.php');
        // $path = realpath(base_path('vendor/facuz/laravel-themes/src/templates/'.$template.'.php'));

        return $this->files->get($path);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Name of the widget to generate.'],
            ['module', InputArgument::REQUIRED, 'Module name to generate widget view file.']
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $path = base_path($this->config->get('theme.themeDir'));

        return [
            ['path', 'p', InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path],
            ['global', 'g', InputOption::VALUE_NONE, 'Create global widget.', null]
        ];
    }
}
