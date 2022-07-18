<?php

namespace SunApp\Modules\Activators;

use Illuminate\Support\Facades\Cache;
use Nwidart\Modules\Activators\FileActivator as BaseFileActivator;
use Nwidart\Modules\Module;
use Str;
use SunApp\Modules\Traits\ModuleHelper;

class FileActivator extends BaseFileActivator
{
    use ModuleHelper;

    /**
     * @inheritDoc
     */
    public function hasStatus(Module $module, bool $status): bool
    {
        $hasStatus = parent::hasStatus($module, $status);
        if (config('ignore_db_status', false)) {
            return $hasStatus;
        }
        if ($status && $hasStatus) {
            $version = $module->get('version', 0);
            if (Str::startsWith($module->getPath(), base_path('vendor')) && $version == 0) {
                $package_name = $module->getComposerAttr('name', false);
                $version = $module->getComposerAttr('version', 0);
                if ($package_name && $version == 0) {
                    $version = $this->getComposerModuleVersion($module, $package_name);
                }
            }/* else {
                $version = $module->getComposerAttr('version', $module->get('version', 0));
            }*/

            $installed_modules = app('installed_modules');
            $installed_module = $installed_modules->where('alias', $module->getAlias())->first();
            if (
                !$installed_module
                || (
                    $installed_module
                    && $installed_module->version != $version
                    && str_replace('9999999-dev', 'dev-master', $installed_module->version)
                    != str_replace('9999999-dev', 'dev-master', $version)
                    && !(
                        $module->getAlias() == 'core'
                        && app()->runningInConsole()
                    )
                )
            ) {
                if ($module->getAlias() == 'core' && $installed_module) {
                    throw new \Exception('Application update required');
                }
                return false;
            }
        }
        return $hasStatus;
    }
}
