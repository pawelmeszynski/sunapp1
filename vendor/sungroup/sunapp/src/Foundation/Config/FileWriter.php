<?php

namespace SunApp\Foundation\Config;

use Illuminate\Filesystem\Filesystem;

class FileWriter
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new configuration file writer instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Generate configuration file from variable.
     *
     * @param $items
     * @return string
     */
    protected function generateConfigurationFile($items)
    {
        $value = '<?php' . PHP_EOL . 'return ' . var_export($items, true) . ';' . PHP_EOL;

        return $value;
    }

    /**
     * Write the given configuration group.
     *
     * @param string $item
     * @param string $value
     * @param string $path
     * @return void
     */
    public function write($item, $value, $path)
    {
        if (str_contains($item, '::')) {
            $ex = explode('::', $item);
            $item = $ex[1];
            $module = \Module::find($ex[0]);
            if ($module) {
                $path = $module->getExtraPath('Config');
            }
        }
        $file = "{$path}/{$item}.php";

        $value = $this->generateConfigurationFile($value);

        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
        $this->files->put($file, $value);
        return;
    }
}
