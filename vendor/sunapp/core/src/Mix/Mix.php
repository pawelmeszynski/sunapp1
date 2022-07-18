<?php

namespace SunAppModules\Core\src\Mix;

use Illuminate\Foundation\Mix as BaseMix;

class Mix extends BaseMix
{
    /**
     * Get the path to a versioned Mix file.
     *
     * @param  string  $path
     * @param  string  $manifestDirectory
     * @return \Illuminate\Support\HtmlString|string
     *
     * @throws \Exception
     */
    public function __invoke($path, $manifestDirectory = '')
    {
        if ($manifestDirectory == '') {
            $manifestDirectory = str_replace('public/', '', \Theme::path());
        }
        return parent::__invoke($path, $manifestDirectory);
    }
}
