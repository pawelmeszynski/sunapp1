<?php

/**
 * Created by IntelliJ IDEA.
 * User: sunpapi
 * Date: 20.02.2019
 * Time: 12:41
 */

if (!function_exists('core_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param string $path
     * @return string
     */
    function core_path($path = '')
    {
        return __DIR__ . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
