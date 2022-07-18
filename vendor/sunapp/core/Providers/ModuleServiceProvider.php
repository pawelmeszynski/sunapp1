<?php

namespace SunAppModules\Core\Providers;

use Config;
use File;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Module;
use ReflectionClass;
use SunAppModules\Core\Entities\NestedModel;
use Symfony\Component\Finder\Finder;
use Theme;

class ModuleServiceProvider extends ServiceProvider
{
    protected $module = null;
    protected $moduleName = null;
    protected $moduleNamespace = null;

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router, Kernel $kernel)
    {
        if (!config('system.front') && class_exists($this->moduleNamespace . 'Http\Middleware\AppMenu')) {
            $router->pushMiddlewareToGroup('web', "\\{$this->moduleNamespace}Http\Middleware\AppMenu");
        }
//        if(class_exists($this->moduleNamespace.'Listeners\AddModulePermissions')){
//            Event::listen(RegisterPermissions::class, $this->moduleNamespace.'Listeners\AddModulePermissions');
//        }
    }

    public function register()
    {
        if ($this->module == null) {
            $module = json_decode(file_get_contents($this->getDir() . '/../module.json'));
            $this->module = $module->alias;
            $this->moduleName = $module->name;
            $this->moduleNamespace = $this->getModuleNamespace();
        }
        $this->registerTranslations();
        $this->mergeConfig();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->registerWidgets();
        $this->loadMigrationsFrom($this->getDir() . '/../Database/Migrations');
    }

    /**
     * Funkcja która dodaje merguje configi.
     * Prawdopodobnie niepotrzebne i do usunięcia.
     */
    protected function mergeConfig()
    {
        if (file_exists($this->getDir() . '/../Config')) {
            $this->mergeConfigFromPath($this->getDir() . '/../Config');
        }
    }

    protected function mergeConfigFromPath($path, $prefix = '')
    {
        foreach (Finder::create()->in($path)->name('*.php') as $file) {
            $this->mergeConfigFrom($file->getRealPath(), $prefix . basename($file->getRealPath(), '.php'));
        }
        $dirs = array_filter(glob($path . '/*'), 'is_dir');
        if (count($dirs) > 0) {
            foreach ($dirs as $dir) {
                $this->mergeConfigFromPath($dir, basename($dir) . '.');
            }
        }
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);
        $this->app['config']->set($key, array_merge($config, require $path));
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        if (file_exists($this->getDir() . '/../Config/config.php')) {
            $this->publishes([
                $this->getDir() . '/../Config/config.php' => config_path($this->module . '.php'),
            ], 'config');
            $this->mergeConfigFrom(
                $this->getDir() . '/../Config/config.php',
                $this->module
            );
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->module);

        $sourcePath = $this->getDir() . '/../Resources/views';
        $widgetPath = $this->getDir() . '/../Resources/widgets';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/' . $this->module;
        }, Config::get('view.paths')), [$sourcePath, $widgetPath]), $this->module);
    }

    public function registerThemes()
    {
        $sourcePath = $this->getDir() . '/../Themes';
        $viewPath = public_path('themes');
        $this->publishes([
            $sourcePath => $viewPath
        ], 'themes');
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->module);
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->module);
        } else {
            $this->loadTranslationsFrom($this->getDir() . '/../Resources/lang', $this->module);
        }
        if (class_exists("\Theme")) {
            $translator = app('translator');
            foreach (Theme::all() as $theme) {
                $translator->addNamespace(
                    $theme . '.' . $this->module,
                    base_path(Theme::path($theme)) . "/modules/{$this->module}/lang"
                );
            }
        }
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        if (!app()->environment('production')) {
            //app(Factory::class)->load($this->getDir() . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    protected function getDir()
    {
        $reflector = new ReflectionClass(get_class($this));
        return dirname($reflector->getFileName());
    }

    public function registerWidgets()
    {
        $theme = app('theme');
        $module_widget = $this->moduleNamespace . 'Widgets';
        config('theme.namespaces.module_widget', $module_widget);
        $theme->getConfigInstance()->set('theme.namespaces.module_widget.' . $this->module, $module_widget);
        $theme->setThemeConfig($theme->getConfigInstance()->get('theme'));
    }

    /**
     * Get module namespace.
     *
     * @param  Module  $module
     *
     * @return string
     */
    public function getModuleNamespace()
    {
        $baseclass = new ReflectionClass(get_class($this));
        $search = $this->moduleName . '\\';
        $from = 0;
        $to = strpos($baseclass->getName(), $search) + strlen($search);
        return substr($baseclass->getName(), $from, $to);
    }

    public function fixTrees($module)
    {
        if (\App::runningInConsole() && in_array('--fix-tree', \Request::server('argv', []))) {
            foreach ($this->getEntities($module) as $key => $entity) {
                $model = new $entity();
                if ($model instanceof NestedModel) {
                    try {
                        DB::beginTransaction();
                        $model::disableAuditing();
                        $model::fixTree();
                        $model::enableAuditing();
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                }
            }
        }
    }

    private function getEntities($module)
    {
        /** @var array $entities */
        $entities = [];

        /** @var File $allFiles */
        $allFiles = File::glob($module->getPath() . '/Entities/*.php');
        foreach ($allFiles as $entity) {
            $entities[pathinfo($entity, PATHINFO_FILENAME)] = 'SunAppModules\\' . $module->getName() . "\Entities\\"
                . pathinfo($entity, PATHINFO_FILENAME);
        }

        return $entities;
    }
}
