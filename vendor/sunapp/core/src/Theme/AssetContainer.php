<?php

namespace SunAppModules\Core\src\Theme;

use Facuz\Theme\AssetContainer as BaseAssetContainer;
use Str;

class AssetContainer extends BaseAssetContainer
{
    /**
     * Add an asset to the container.
     *
     * The extension of the asset source will be used to determine the type of
     * asset being registered (CSS or JavaScript). When using a non-standard
     * extension, the style/script methods may be used to register assets.
     *
     * <code>
     *      // Add an asset to the container
     *      Asset::container()->add('jquery', 'js/jquery.js');
     *
     *      // Add an asset that has dependencies on other assets
     *      Asset::add('jquery', 'js/jquery.js', 'jquery-ui');
     *
     *      // Add an asset that should have attributes applied to its tags
     *      Asset::add('jquery', 'js/jquery.js', null, array('defer'));
     * </code>
     *
     * @param  string  $name
     * @param  string  $source
     * @param  array  $dependencies
     * @param  array  $attributes
     * @return AssetContainer
     */
    protected function added($name, $source, $dependencies = [], $attributes = [])
    {
        if (is_array($source)) {
            foreach ($source as $path) {
                $name = $name . '-' . md5($path);

                $this->added($name, $path, $dependencies, $attributes);
            }
        } else {
            $ext = pathinfo(strtok($source, '?'), PATHINFO_EXTENSION);
            if ($ext == '') {
                $ext = substr(strtok($source, '?'), -3, 3);
            }
            $type = ($ext == 'css') ? 'style' : 'script';
            // Remove unnecessary slashes from internal path.
            if (!preg_match('|^//|', $source)) {
                $source = ltrim($source, '/');
            }

            return $this->$type($name, $source, $dependencies, $attributes);
        }
    }

    /**
     * Add an asset to the array of registered assets.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $source
     * @param  array  $dependencies
     * @param  array  $attributes
     */
    protected function register($type, $name, $source, $dependencies, $attributes)
    {

        $dependencies = (array)$dependencies;

        $attributes = (array)$attributes;
        if (
            !str_contains($source, '</script>') &&
            !str_contains($source, '</style>') &&
            !Str::startsWith($source, 'http')
        ) {
            $theme = resolve('theme');
            if ($theme) {
                $source .= (str_contains($source, '?') ? '&' : '?') . '_tv=' . $theme->info('version');
            }
        }
        $this->assets[$type][$name] = compact('source', 'dependencies', 'attributes');
    }

    /**
     * Render asset as HTML.
     *
     * @param  string $group
     * @param  mixed  $source
     * @param  array  $attributes
     * @return string
     */
    public function html($group, $source, $attributes)
    {
        $result = substr($source, 0, strlen(env('APP_PUBLIC_DIR', 'public')) + 1);
        if ($result == '/' . env('APP_PUBLIC_DIR', 'public')) {
            $source = substr($source, strlen(env('APP_PUBLIC_DIR', 'public')) + 1);
        }

        $source = url($source);

        switch ($group) {
            case 'script':
                $attributes['src'] = $source;
                return '<script' . $this->attributes($attributes) . '></script>' . PHP_EOL;
            case 'style':
                $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];

                $attributes = $attributes + $defaults;

                $attributes['href'] = $source;

                return '<link' . $this->attributes($attributes) . '>' . PHP_EOL;
        }
    }

    /**
     * Return asset path with current theme path.
     *
     * @param  string  $uri
     * @param  bool $secure
     * @return string
     */
    public function url($uri, $secure = null)
    {
        // If path is full, so we just return.
        if (preg_match('#^http|//:#', $uri)) {
            return $uri;
        }

        $path = $this->getCurrentPath() . $uri;

        if (substr($path, 0, strlen(env('APP_PUBLIC_DIR', 'public'))) == env('APP_PUBLIC_DIR', 'public')) {
            $path = substr($path, strlen(env('APP_PUBLIC_DIR', 'public')) + 1);
        }

        return $this->configAssetUrl($path, $secure);
    }

    /**
     * Return asset absolute path with current theme path.
     *
     * @param  string  $uri
     * @param  bool $secure
     * @return string
     */
    public function absUrl($uri, $secure = null)
    {
        $source = $this->url($uri, $secure);

        if (substr($source, 0, strlen(env('APP_PUBLIC_DIR', 'public'))) == '/' . env('APP_PUBLIC_DIR', 'public')) {
            $source = substr($source, strlen(env('APP_PUBLIC_DIR', 'public')) + 1);
        }

        $theme = resolve('theme');
        if ($theme) {
            $source .= (str_contains($source, '?') ? '&' : '?') . '_tv=' . $theme->info('version');
        }

        return url($source);
    }

    /**
     * Add a CSS file to the registered assets.
     *
     * @param  string           $name
     * @param  string           $source
     * @param  array            $dependencies
     * @param  array            $attributes
     * @return AssetContainer
     */
    public function style($name, $source, $dependencies = array(), $attributes = array())
    {
        if (!array_key_exists('media', $attributes)) {
            $attributes['media'] = 'all';
        }

        // Prepend path to theme.
        if ($this->isUsePath()) {
            $source = $this->evaluatePath($this->getCurrentPath() . $source);
        }

        if (env('APP_MINIFY_ASSETS') && strpos($source, '.min.css') === false) {
            $this->register('style', $name, str_replace('.css', '.min.css', $source), $dependencies, $attributes);
        } else {
            $this->register('style', $name, $source, $dependencies, $attributes);
        }

        return $this;
    }

    /**
     * Add a JavaScript file to the registered assets.
     *
     * @param  string           $name
     * @param  string           $source
     * @param  array            $dependencies
     * @param  array            $attributes
     * @return AssetContainer
     */
    public function script($name, $source, $dependencies = array(), $attributes = array())
    {
        // Prepend path to theme.
        if ($this->isUsePath()) {
            $source = $this->evaluatePath($this->getCurrentPath() . $source);
        }

        if (env('APP_MINIFY_ASSETS') && strpos($source, '.min.js') === false) {
            $this->register('script', $name, str_replace('.js', '.min.js', $source), $dependencies, $attributes);
        } else {
            $this->register('script', $name, $source, $dependencies, $attributes);
        }

        return $this;
    }
}
