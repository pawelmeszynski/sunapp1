<?php

namespace SunAppModules\Core\src\Translation;

use Illuminate\Support\ServiceProvider;
use SunApp\Foundation\Translation\FileLoader;
use Theme;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $paths = app('translation.loader')->getPath();
        $this->app->extend('translation.loader', function ($loader, $app) use ($paths) {
            foreach (Theme::all() as $theme) {
                $paths[] = base_path(Theme::path($theme)) . '/lang';
            }
            $new_loader = new FileLoader($app['files'], $paths);
            foreach ($loader->namespaces() as $namespace => $hint) {
                $new_loader->addNamespace($namespace, $hint);
            }
            return $new_loader;
        });

        $this->app->extend('translator', function ($command, $app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }
}
