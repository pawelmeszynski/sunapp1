<?php

namespace SunAppModules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SunAppModules\Core\Entities\Modules;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('2fa');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        return response()->redirectToRoute('SunApp::dashboard');
//        return theme_view('core::home.index');
    }

    public function getVersionData()
    {
        $results = DB::select(DB::raw('SHOW VARIABLES LIKE "version"'));
        $dbVersion = $results[0]->Value;
        $dbType = config('database.default');
        $serverVersion = $_SERVER['SERVER_SOFTWARE'];
        $modules = Modules::get();
        $installedModules = [];
        foreach ($modules as $module) {
            $moduleData = \Module::find($module->name);
            if ($moduleData && $moduleData->isEnabled()) {
                $installedModules[] = $module->name;
            }
        }
        $requiredExtensions = Cache::rememberForever('system_info:installed_extensions', function () {
            $composerPath = base_path('composer.lock');
            $composerContent = file_get_contents($composerPath);
            $composerContent = json_decode($composerContent, true);
            $requiredExtensions = [];
            if (isset($composerContent['packages'])) {
                foreach ($composerContent['packages'] as $package) {
                    if (isset($package['require'])) {
                        $requiredPackageExtensions = array_filter(
                            $package['require'],
                            function ($key) {
                                return Str::startsWith($key, 'ext-');
                            },
                            ARRAY_FILTER_USE_KEY
                        );
                        if (count($requiredPackageExtensions)) {
                            $requiredExtensions = array_merge($requiredExtensions, $requiredPackageExtensions);
                        }
                    }
                }
            }
            $requiredExtensions = array_keys($requiredExtensions);
            return $requiredExtensions;
        });
        return view('core::home.version-info')
            ->with('dbVersion', $dbVersion)
            ->with('dbType', $dbType)
            ->with('serverVersion', $serverVersion)
            ->with('installedModules', $installedModules)
            ->with('requiredExtensions', $requiredExtensions);
    }
}
