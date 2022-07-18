<?php

namespace SunApp\Foundation\Config;

use Illuminate\Filesystem\Filesystem;

class CacheManager
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The configuration files path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a configuration cache manager.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param String $path
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->files = $files;
        $this->path = $path;
    }

    /**
     * Determine if the application configuration is cached.
     *
     * @return bool
     */
    public function isCached()
    {
        return $this->files->exists($this->path);
    }

    /**
     * Clear cached configuration files.
     *
     * @return void
     */
    public function clear()
    {
        $this->files->delete($this->path);
    }

    /**
     * Refresh cached configuration files.
     *
     * @param array $config
     * @return void
     */
    public function refresh(array $config)
    {
        if (!$this->isCached()) {
            return;
        }

        $this->clear();

        if (!$this->files->isDirectory($this->path)) {
            $this->files->makeDirectory($this->path, 0755, true);
        }
        $this->files->put(
            $this->path,
            '<?php return ' . var_export($config, true) . ';' . PHP_EOL
        );
    }
}
