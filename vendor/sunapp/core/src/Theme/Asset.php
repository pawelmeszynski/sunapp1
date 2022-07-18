<?php

namespace SunAppModules\Core\src\Theme;

use Facuz\Theme\Asset as BaseAsset;

class Asset extends BaseAsset
{
    /**
     * Get an asset container instance.
     *
     * <code>
     *        // Get the default asset container
     *        $container = Asset::container();
     *
     *        // Get a named asset container
     *        $container = Asset::container('footer');
     * </code>
     *
     * @param  string  $container
     * @return AssetContainer
     */
    public static function container($container = 'default')
    {
        if (!isset(static::$containers[$container])) {
            static::$containers[$container] = new AssetContainer($container);
        }

        return static::$containers[$container];
    }
}
