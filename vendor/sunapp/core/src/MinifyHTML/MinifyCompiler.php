<?php

namespace SunAppModules\Core\src\MinifyHTML;

use HTMLMin\HTMLMin\Minifiers\BladeMinifier;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

class MinifyCompiler extends BladeCompiler
{
    /**
     * The blade minifier instance.
     *
     * @var \HTMLMin\HTMLMin\Minifiers\BladeMinifier
     */
    protected $blade;

    /**
     * The ignored paths.
     *
     * @var string
     */
    protected $ignoredPaths;

    /**
     * Create a new instance.
     *
     * @param \HTMLMin\HTMLMin\Minifiers\BladeMinifier $blade
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param string $cachePath
     * @param array $ignoredPaths
     */
    public function __construct(
        BladeMinifier $blade,
        Filesystem $files,
        $cachePath,
        $ignoredPaths = [],
        $customDirectives = []
    ) {
        parent::__construct($files, $cachePath);
        $this->blade = $blade;
        $this->ignoredPaths = $ignoredPaths;
        $this->compilers[] = 'Minify';

        if (count($customDirectives)) {
            foreach ($customDirectives as $key => $value) {
                $this->directive($key, $value);
            }
        }
    }

    /**
     * Minifies the output before saving it.
     *
     * @param string $value
     *
     * @return string
     */
    public function compileMinify($value)
    {
        if ($this->ignoredPaths) {
            $path = str_replace('\\', '/', $this->getPath());

            foreach ($this->ignoredPaths as $ignoredPath) {
                if (strpos($path, $ignoredPath) !== false) {
                    return $value;
                }
            }
        }

        return $this->blade->render($value);
    }

    /**
     * Return the compilers.
     *
     * @return string[]
     */
    public function getCompilers()
    {
        return $this->compilers;
    }

    /**
     * Return the blade minifier instance.
     *
     * @return \HTMLMin\HTMLMin\Minifiers\BladeMinifier
     */
    public function getBladeMinifier()
    {
        return $this->blade;
    }
}
