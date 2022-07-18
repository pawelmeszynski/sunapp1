<?php

namespace SunAppModules\Core\src\Theme\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem as File;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class WidgetGeneratorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:widget';

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
        if ($this->option('global') === false and !$this->argument('theme')) {
            return $this->error('Please specific a theme name or use option -g to create as a global widget.');
        }

        $theme_path = base_path($this->config->get('theme.themeDir') . '/' . $this->getTheme());

        if (is_dir($theme_path) === false) {
            return $this->error('The theme "' . $this->getTheme() . '" does not exist.');
        }

        // Create as a global use -g.
        if ($this->option('global') === true) {
            $watch = 'true';
        }

        $widgetNamespace = $this->config->get('theme.namespaces.widget');
        // Prepare class template.
        $widgetClassTemplate = preg_replace(
            ['|\{widgetNamespace\}|', '|\{widgetClass\}|', '|\{widgetTemplate\}|', '|\{watch\}|'],
            [$widgetNamespace, $widgetClassName, $widgetClassTpl, $watch],
            $widgetClassTemplate
        );

        // Create widget directory.
        if (!$this->files->isDirectory(app_path() . '/Widgets')) {
            $this->files->makeDirectory(app_path() . '/Widgets', 0777, true);
        }

        // Widget class already exists.
        if ($this->files->exists(app_path() . '/Widgets/' . $widgetClassFile)) {
            return $this->error('Widget "' . $this->getWidgetName() . '" is already exists.');
        }

        // Create class file.
        $this->files->put(app_path() . '/Widgets/' . $widgetClassFile, $widgetClassTemplate);

        // Make file example.
        $this->makeFile('widgets/' . $widgetClassTpl . '.blade.php', $this->getTemplate('widget.blade'));

        $this->info('Widget "' . $this->getWidgetName() . '" has been created.');
    }

    /**
     * Make file.
     *
     * @param  string  $file
     * @param  string  $template
     */
    protected function makeFile($file, $template = null)
    {
        $dirname = dirname($this->getPath($file));
        // Checking directory.
        if (!$this->argument('theme') and !$this->files->isDirectory($dirname)) {
            $this->files->makeDirectory($dirname, 0777, true);
        }

        if (!$this->files->isDirectory($dirname)) {
            $this->files->makeDirectory($dirname, 0777, true);
        }

        if (!$this->files->exists($this->getPath($file))) {
            $this->files->put($this->getPath($file), $template);
        }
    }

    /**
     * Get root writable path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getPath($path)
    {
        // If not specific theme name, so widget will creating as global.
        if (!$this->argument('theme')) {
            return base_path('resources/views/' . $path);
        }

        $rootPath = $this->option('path');

        return $rootPath . '/' . $this->getTheme() . '/' . $path;
    }

    /**
     * Get the widget name.
     *
     * @return string
     */
    protected function getWidgetName()
    {
        // The first character must be lower.
        $val = preg_replace_callback('/(?:^|[-_])([a-z])/', function ($m) {
            return strtoupper($m[1]);
        }, $this->argument('name'));
        return $val;
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
            ['theme', InputArgument::OPTIONAL, 'Theme name to generate widget view file.']
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
