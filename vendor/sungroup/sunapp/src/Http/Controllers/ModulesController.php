<?php

namespace SunApp\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use SunApp\Modules\IO\AjaxIO;
use SunApp\Modules\Process\Installer;
use SunApp\Modules\Process\Uninstaller;
use SunApp\Modules\Process\Updater;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ModulesController extends BaseController
{
    public function index()
    {
        return 'modules';
    }

    public function install(Request $request)
    {
        $encrypted_package = $request->get('package', false);
        if (!$encrypted_package) {
            return abort(422, trans('modules.no_package_name'));
        }
        list($name, $version) = $this->getDecodePackage($encrypted_package);

        if (!$request->acceptsHtml()) {
            $response = new StreamedResponse(function () use ($request, $name, $version) {
                try {
                    $output = new AjaxIO();
                    $installer = new Installer(
                        $name,
                        $version,
                        $request->get('type', 'composer'),
                        false
                    );

                    $installer->setRepository(app('modules'));

                    $installer->setOutput($output);

                    $installer->run();

                    echo sprintf("event:%s\n", strtr('close', ["\r" => ' ', "\n" => ' ']));
                    echo 'data: ' . json_encode([]) . "\n\n";
                    ob_flush();
                    flush();
                } catch (Exception $e) {
                    \Log::error($e->getMessage(), $e);
                    echo sprintf("event:%s\n", strtr('error', ["\r" => ' ', "\n" => ' ']));
                    echo 'data: ' . json_encode(['message' => $e->getMessage()]) . "\n\n";
                    ob_flush();
                    flush();
                }
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cach-Control', 'no-cache');
            return $response;
        } else {
            $installed = false;
            $module = app('modules')->find($this->getModuleName($name));
            if ($module && $module->version == $module->getVersions('publishes')) {
                $installed = true;
            }
            return view('modules.install', [
                'package' => $request->get('package', false),
                'url' => $request->get('url', false),
                'installed' => $installed,
                'package_name' => $name
            ]);
        }
    }

    public function update(Request $request)
    {
        $encrypted_package = $request->get('package', false);
        if (!$encrypted_package) {
            return abort(422, trans('modules.no_package_name'));
        }
        list($name, $version) = $this->getDecodePackage($encrypted_package);

        if (!$request->acceptsHtml()) {
            $response = new StreamedResponse(function () use ($request, $name, $version) {
                try {
                    $output = new AjaxIO();
                    $installer = new Updater(
                        $name,
                        $version,
                        $request->get('type', 'composer'),
                        false
                    );

                    $installer->setRepository(app('modules'));

                    $installer->setOutput($output);

                    $installer->run();

                    echo sprintf("event:%s\n", strtr('close', ["\r" => ' ', "\n" => ' ']));
                    echo 'data: ' . json_encode([]) . "\n\n";
                    ob_flush();
                    flush();
                } catch (Exception $e) {
                    echo sprintf("event:%s\n", strtr('error', ["\r" => ' ', "\n" => ' ']));
                    echo 'data: ' . json_encode(['message' => $e->getMessage()]) . "\n\n";
                    ob_flush();
                    flush();
                }
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cach-Control', 'no-cache');
            return $response;
        } else {
            $installed = false;
            return view('modules.update', [
                'package' => $request->get('package', false),
                'url' => $request->get('url', false),
                'installed' => $installed,
                'package_name' => $name
            ]);
        }
    }

    public function uninstall(Request $request)
    {
        $encrypted_package = $request->get('package', false);
        if (!$encrypted_package) {
            return abort(422, trans('modules.no_package_name'));
        }
        list($name, $version) = $this->getDecodePackage($encrypted_package);

        if (!$request->acceptsHtml()) {
            $response = new StreamedResponse(function () use ($request, $name, $version) {
                try {
                    $output = new AjaxIO();
                    $uninstaller = new Uninstaller(
                        $name,
                        $version,
                        $request->get('type', 'composer'),
                        false
                    );

                    $uninstaller->setRepository(app('modules'));

                    $uninstaller->setOutput($output);

                    $uninstaller->run();

                    echo sprintf("event:%s\n", strtr('close', ["\r" => ' ', "\n" => ' ']));
                    echo 'data: ' . json_encode([]) . "\n\n";
                    ob_flush();
                    flush();
                } catch (Exception $e) {
                    echo sprintf("event:%s\n", strtr('error', ["\r" => ' ', "\n" => ' ']));
                    echo 'data: ' . json_encode(['message' => $e->getMessage()]) . "\n\n";
                    ob_flush();
                    flush();
                }
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cach-Control', 'no-cache');
            return $response;
        } else {
            $installed = false;
            $module = app('modules')->find($this->getModuleName($name));
            if ($module && $module->version == $module->getVersions('publishes')) {
                $installed = true;
            }
            return view('modules.uninstall', [
                'package' => $request->get('package', false),
                'url' => $request->get('url', false),
                'installed' => $installed,
                'package_name' => $name
            ]);
        }
    }

    public function enable(Request $request)
    {
        $encrypted_module = $request->get('module', false);
        if (!$encrypted_module) {
            return abort(422, trans('modules.no_module_name'));
        }
        $back_url = $request->get('url', false);
        $steps = [];
        $module = false;
        $installed = false;

        list($module_name, $module_version) = $this->getDecodeModule($encrypted_module);
        $parts = explode('/', $module_name);
        $module_name = Str::lower(end($parts));

        $module = app('modules')->findOrFail($module_name);
        $enabled = $module->enabled();
        if (!$enabled) {
            if (($module && $module->install_version != $module->getVersions('configs')) || !$module) {
                if ($module->composer_package != '') {
                    return redirect()->route(
                        'AppModules::composer.install',
                        ['package' => Crypt::encryptString('sunapp/core'), 'url' => $back_url]
                    );
                }
                return redirect()->to($back_url)->withErrors(['message', trans('modules.module_status_error')]);
            }
            $module->enable();
            $enabled = $module->enabled();
        }

        if ($back_url) {
            if ($enabled) {
                return redirect()->to($back_url)->with(['message', trans('modules.module_enabled')]);
            }
            return redirect()->to($back_url)->withErrors(['message', trans('modules.module_status_error')]);
        }
    }

    public function disable(Request $request)
    {
        $encrypted_module = $request->get('module', false);
        if (!$encrypted_module) {
            return abort(422, trans('modules.no_module_name'));
        }
        $back_url = request()->get('url', false);
        list($package_name, $version) = $this->getDecodePackage($encrypted_module);
        $module = app('modules')->findOrFail($package_name);
        $disabled = $module->disabled();
        if (!$disabled) {
            $module->disable();
            $disabled = $module->disabled();
        }
        if ($back_url) {
            if ($disabled) {
                return redirect()->to($back_url)->with(['message', trans('modules.module_disabled')]);
            }
            return redirect()->to($back_url)->withErrors(['message', trans('modules.module_status_error')]);
        }
    }

    private function getDecodePackage($encrypted_module)
    {
        $package = Crypt::decryptString($encrypted_module);
        $version = '*';
        if (Str::contains($package, ':')) {
            $ex = explode(':', $package);
            $package = $ex[0];
            $version = $ex[1];
        }
        return [$package, $version];
    }

    /**
     * Get module name.
     *
     * @param string $name
     * @return string
     */
    public function getModuleName($name = '')
    {
        $parts = explode('/', $name);

        return Str::studly(end($parts));
    }

    private function getDecodeModule($encrypted_module)
    {
        $package = Crypt::decryptString($encrypted_module);
        $version = '*';
        if (str_contains($package, ':')) {
            $ex = explode(':', $package);
            $package = $ex[0];
            $version = $ex[1];
        }
        return [$package, $version];
    }
}
