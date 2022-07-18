<?php

namespace SunAppModules\Core\Http\Controllers;

use Illuminate\Http\Request;
use SunApp\Http\Controllers\Controller as BaseController;
use Artisan;

class CacheController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }

    public function cacheClear()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return redirect()->back();
    }
}
