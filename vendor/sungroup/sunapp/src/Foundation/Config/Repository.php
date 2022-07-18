<?php

/**
 * Created by SunGroup.
 * User: patryk.piotrowski
 * Date: 07.09.2018
 * Time: 21:18
 */

namespace SunApp\Foundation\Config;

use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

class Repository extends BaseRepository
{
    /**
     * The configuration files path.
     *
     * @var string
     */
    protected $path;

    /**
     * The configuration files path.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Set a given running configuration write in configuration file.
     *
     * @param string $key
     * @param string $path
     * @return void
     */
    public function save($key, $path = null)
    {
        $files = new Filesystem();

        $fileWriter = new FileWriter($files);
        $fileWriter->write($key, $this->get($key), $path ?: $this->path);

        $cacheManager = new CacheManager($files, $this->cachePath);
        $cacheManager->refresh($this->items);

        $this->fireWritingEvent();
    }

    /**
     * Set a given running configuration write in configuration file.
     *
     * @param string $key
     * @param string $config
     * @param string $path
     * @return void
     */
    public function saveAs($key, $config = null, $path = null)
    {
        $files = new Filesystem();

        $fileWriter = new FileWriter($files);
        $fileWriter->write($key, $this->get($config), $path ?: $this->path);

        $cacheManager = new CacheManager($files, $this->cachePath);
        $cacheManager->refresh($this->items);

        $this->fireWritingEvent();
    }

    /**
     * Register an config write event listener.
     *
     * @param mixed $callback
     * @return void
     */
    public function writing($callback)
    {
        if (isset($this->events)) {
            $this->events->listen('config.write', $callback);
        }
    }

    /**
     * Fire the writing event if the dispatcher is set.
     *
     * @return void
     */
    protected function fireWritingEvent()
    {
        if (isset($this->events)) {
            $this->events->fire('config.write');
        }
    }

    /**
     * Get the configuration files path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the cached configuration files path.
     *
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the cached configuration files path.
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Set the configuration files path.
     *
     * @param string $cachePath
     * @return void
     */
    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher
     * @return void
     */
    public function setDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }
}
