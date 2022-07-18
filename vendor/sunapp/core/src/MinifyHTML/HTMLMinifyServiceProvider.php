<?php

namespace SunAppModules\Core\src\MinifyHTML;

use Illuminate\Contracts\Container\Container;

class HTMLMinifyServiceProvider extends \HTMLMin\HTMLMin\HTMLMinServiceProvider
{
    /**
     * Register the minify compiler class.
     */
    protected function registerMinifyCompiler()
    {
        $this->app->singleton('htmlmin.compiler', function (Container $app) {
            $oldCompiler = $app['blade.compiler'];
            $blade = $app['htmlmin.blade'];
            $files = $app['files'];
            $storagePath = $app->config->get('view.compiled');
            $ignoredPaths = $app->config->get('htmlmin.ignore', []);

            return new MinifyCompiler($blade, $files, $storagePath, $ignoredPaths, $oldCompiler->getCustomDirectives());
        });

        $this->app->alias('htmlmin.compiler', MinifyCompiler::class);
    }
}
