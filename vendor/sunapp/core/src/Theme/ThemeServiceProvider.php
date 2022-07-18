<?php

namespace SunAppModules\Core\src\Theme;

use Facuz\Theme\ThemeServiceProvider as BaseThemeServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use SunAppModules\Core\src\View\Factory;

//use Illuminate\View\Factory;

class ThemeServiceProvider extends BaseThemeServiceProvider
{
    public function register()
    {
        parent::register();
        $this->registerModuleWidgetGenerator();
        $this->commands([
            'theme.module-widget'
        ]);
    }

    public function boot(Router $router)
    {
        parent::boot($router);
        //$this->addToBlade(['theme', 'Theme::theme(%s);']);
        //$this->addToBlade(['layout', 'Theme::layout(%s);']);
        Blade::directive('svg', function ($src) {
            return "<?php" . PHP_EOL
                . "\$src = " . $src . ";" . PHP_EOL
                . "echo \Illuminate\Support\Facades\Cache::remember(" . PHP_EOL
                . "     'svg_'.md5(\$src)," . PHP_EOL
                . "     null," . PHP_EOL
                . "     function () use (\$src) {" . PHP_EOL
                . "         \$path = \Str::startsWith(\$src, 'http')" . PHP_EOL
                . "             ? \$src" . PHP_EOL
                . "             : public_path(\Theme::asset()->themePath()->url(\$src));" . PHP_EOL
                . "         \$url = \Str::startsWith(\$src, 'http')" . PHP_EOL
                . "             ? \$src" . PHP_EOL
                . "             : \Theme::asset()->themePath()->url(\$src);" . PHP_EOL
                . "         try {" . PHP_EOL
                . "             return file_get_contents(\$path);" . PHP_EOL
                . "         } catch (\Exception \$e) {" . PHP_EOL
                . "             return '<img src=\"'.url(\$url).'\">';" . PHP_EOL
                . "         }" . PHP_EOL
                . "});" . PHP_EOL
                . "?>" . PHP_EOL;
        });
        Blade::directive('theme', function ($data) {
            return "<?php Theme::theme({$data}); ?>";
        });
        Blade::directive('layout', function ($data) {
            return "<?php Theme::layout({$data}); ?>";
        });
        Blade::directive('set', function ($data) {
            return "<?php Theme::set({$data}); ?>";
        });
        Blade::directive('locale', function ($data) {
            return "<?php echo str_replace('_', '-', app()->getLocale()); ?>";
        });
        Blade::directive('localeFallback', function ($data) {
            return "<?php echo str_replace('_', '-', app('translator')->getFallback()); ?>";
        });
        Blade::directive('csrfToken', function ($data) {
            return '<?php echo csrf_token(); ?>';
        });
    }

    /**
     * Register theme provider.
     */
    public function registerTheme()
    {
        $app = app();
        $resolver = $app['view.engine.resolver'];
        $finder = $app['view.finder'];
        $env = new Factory($resolver, $finder, $app['events']);
        $env->setContainer($app);
        // Share variables
        $env->share('app', $app);
        // Create Theme instance
        $this->app->singleton('asset', function ($app) {
            return new Asset();
        });
        $this->app->singleton('theme', function ($app) use ($env) {
            $theme = new Theme(
                $app['config'],
                $app['events'],
                $env,
                $app['asset'],
                $app['files'],
                $app['breadcrumb'],
                $app['manifest']
            );

            $hints[] = base_path($theme->path());
            // This is nice feature to use inherit from another.
            if ($theme->getConfig('inherit')) {
                // Inherit from theme name.
                $inherit = $theme->getConfig('inherit');

                // Inherit theme path.
                $inheritPath = base_path($theme->path($inherit));

                if ($theme->getFiles()->isDirectory($inheritPath)) {
                    array_push($hints, $inheritPath);
                }
            }
            if ($theme->getConfig('inherit_assets')) {
                app('asset')::$path = $theme->path($inherit) . '/assets/';
            }

            foreach ($hints as $hint) {
                $env->addLocation($hint . '/views');
            }
            return $theme;
        });

        $this->app->alias('theme', 'Facuz\Theme\Contracts\Theme');
    }

    /**
     * Register generator of widget.
     */
    public function registerWidgetGenerator()
    {
        $this->app->singleton('theme.widget', function ($app) {
            return new Commands\WidgetGeneratorCommand($app['config'], $app['files']);
        });
    }

    /**
     * Register generator of module widget.
     */
    public function registerModuleWidgetGenerator()
    {
        $this->app->singleton('theme.module-widget', function ($app) {
            return new Commands\ModuleWidgetGeneratorCommand($app['config'], $app['files']);
        });
    }
}
