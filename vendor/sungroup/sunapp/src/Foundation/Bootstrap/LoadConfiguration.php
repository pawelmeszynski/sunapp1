<?php

namespace SunApp\Foundation\Bootstrap;

use Exception;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;
use Symfony\Component\Finder\Finder;

class LoadConfiguration extends BaseLoadConfiguration
{
    /**
     * Load the configuration items from all of the files.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\Config\Repository $repository
     * @return void
     * @throws \Exception
     */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        $core_files = $this->getCoreConfigurationFiles();
        if (is_dir(realpath($app->configPath()))) {
            $app_files = $this->getConfigurationFiles($app);
        } else {
            $app_files = [];
        }
        $files = array_merge($core_files, $app_files);

        if (!isset($files['app'])) {
            throw new Exception('Unable to load the "app" configuration file.');
        }

        foreach ($files as $key => $path) {
            $data = [];
            if (isset($core_files[$key])) {
                $data = require $core_files[$key];
            }
            $data_2 = [];
            if (isset($app_files[$key])) {
                $data_2 = require $app_files[$key];
            }
            $repository->set($key, array_merge($data, $data_2));
        }
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @return array
     */
    protected function getCoreConfigurationFiles()
    {
        $files = [];
        $configPath = core_path('config');
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }
}
