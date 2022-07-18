<?php

namespace SunApp\Modules\Traits;

use Illuminate\Support\Facades\Cache;

trait ModuleHelper
{
    /**
     * @param $module
     * @param $moduleName
     * @return void
     */
    public function getComposerModuleVersion($module, $moduleName): string
    {
        $getComposerModulesVersions = $this->getComposerModulesVersions();

        $version = 0;
        if (isset($getComposerModulesVersions[$moduleName])) {
            $version = $getComposerModulesVersions[$moduleName];
        }
        return $version;
    }

    /**
     * @return array|mixed
     */
    public function getComposerModulesVersions(): array
    {
        $cacheKey = 'composer_module_versions';
        $getComposerModuleVersions = Cache::get($cacheKey);
        $versions = [];
        if (!$getComposerModuleVersions) {
            $composerPath = base_path('composer.lock');
            $composerContent = file_get_contents($composerPath);
            $composerContent = json_decode($composerContent, true);
            $composerPackages = collect($composerContent['packages']);
            $modules = $composerPackages->where('type', 'sunapp-module');
            foreach ($modules as $module) {
                $versions[$module['name']] = $module['version'] ?? 0;
            }
            Cache::put($cacheKey, json_encode($versions));
        } else {
            $versions = json_decode($getComposerModuleVersions, true);
        }
        return $versions;
    }
}
