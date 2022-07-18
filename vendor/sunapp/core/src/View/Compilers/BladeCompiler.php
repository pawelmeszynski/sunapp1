<?php

/**
 * Created by SunGroup.
 * User: patryk.piotrowski
 * Date: 30.01.2018
 * Time: 09:37
 */

namespace SunAppModules\Core\src\View\Compilers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class BladeCompiler extends \Illuminate\View\Compilers\BladeCompiler
{
    /**
     * Compile the view at the given path.
     *
     * @param  string  $path
     * @param  bool  $debug
     * @throws FileNotFoundException
     */
    public function compile($path = null, $debug = false)
    {
        if ($path) {
            $this->setPath($path);
        }

        $debug = config('app.blade_debug', $debug);
        if (!is_null($this->cachePath)) {
            $string = '';
            if ($debug) {
                $string .= (!str_contains(
                    $this->getPath(),
                    'script'
                ) ? '<?php echo "<!-- START: ' . $this->getPath() . ' -->"; ?>' : '');
            }
            $string .= $this->files->get($this->getPath());
            if ($debug) {
                $string .= (!str_contains(
                    $this->getPath(),
                    'script'
                ) ? '<?php echo "<!-- STOP: ' . $this->getPath() . ' -->"; ?>' : '');
            }
            $contents = $this->compileString($string);

            $this->files->put($this->getCompiledPath($this->getPath()), $contents);
        }
    }
}
