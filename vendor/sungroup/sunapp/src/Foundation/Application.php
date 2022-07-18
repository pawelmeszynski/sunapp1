<?php

namespace SunApp\Foundation;

use Illuminate\Foundation\Application as BaseApplication;
use RuntimeException;

class Application extends BaseApplication
{
    public const SUNAPP_VERSION = '5.0.000';
    protected $namespace = 'App\\';

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        if (!is_dir(config('view.compiled'))) {
            mkdir(config('view.compiled'), 0777, true);
        }
        if (!is_dir(config('session.files'))) {
            mkdir(config('session.files'), 0777, true);
        }
        if (
            config('cache.stores.' . config('cache.default', '') . '.path', '')
            != '' && !is_dir(config('cache.stores.' . config('cache.default') . '.path'))
        ) {
            mkdir(config('cache.stores.' . config('cache.default') . '.path'), 0777, true);
        }
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Set the application directory.
     *
     * @param string $path
     * @return \Illuminate\Foundation\Application
     */
    public function useBasePath($path)
    {
        $this->basePath = $path;
        return $this;
    }
}
