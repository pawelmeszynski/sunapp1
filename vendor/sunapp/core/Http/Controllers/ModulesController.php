<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use Illuminate\Http\Request;
use Nwidart\Modules\Json;
use SunAppModules\Core\Entities\Modules;
use SunAppModules\Core\Forms\UserForm;
use SunAppModules\Core\Repositories\Repository;
use Symfony\Component\Process\Process;

class ModulesController extends Controller
{
    protected $prefix = "core::modules";

    protected $packageName = "";
    protected $class = Modules::class;
    protected $formClass = UserForm::class;

    public function __construct(Repository $repository)
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;
        $this->repository->setModel(Modules::class)->setSearchable([
            'name' => 'like'
        ]);
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return Response|View|View
     */
    public function index(Request $request)
    {
        if (!Bouncer::can('show', $this->class)) {
            abort(403);
        }

        if ($request->ajax()) {
            $modules = Modules::getModuleStatuses();

            return $this->repository->scopeQuery(function ($query) use ($modules) {
                foreach ($modules as $k => $v) {
                    $query->orWhere('name', $k);
                }
                return $query;
            })->paginate();

            return $this->repository->paginate();
        }

        $this->prepareForm();

        return theme_view(
            $this->prefix . '.index',
            [
                'items' => $this->items,
                'form' => $this->itemForm,
                'item' => $this->item
            ]
        );
    }

    public function enable(Request $request)
    {
        $data = $request->all();
        $res = $this->changeStatusModule($data['status'], $data['name'], $data['type']);
    }

    private function changeStatusModule($status, $name, $type)
    {
        if (!file_exists($path = storage_path('app/modules_statuses.json'))) {
            $this->error("File 'modules_statuses.json' does not exist in your project root.");
            return;
        }
        $json = new Json($path);
        $modules = Json::make($path);

        try {
            $all_modules = $modules->all();
            // wylaczanie glownego modulu
            $all_modules[$name] = $status;

            if ($type == 'disableModule') {
                $findOthersModule = $this->repository->findByField('name', $name);
                $checkWhereUsedModule = $findOthersModule['data'][0]['attributes']['checkWhereUsedModule'];
                // wylaczenie innych modulow
                foreach ($checkWhereUsedModule as $check) {
                    $all_modules[$check] = false;
                }
            }

            $res = $json->update($all_modules);
        } catch (Exception $e) {
            $this->error("Cannot change status module", $e);
        }
        return $res;
    }

    public function getModulesFromComposer(Request $request)
    {
        putenv('COMPOSER_HOME=' . storage_path('app/composer'));
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');
        $io = new \Composer\IO\NullIO();
        $factory = new \Composer\Factory();
        $composer = $factory->createComposer($io, base_path('composer.json'), false, base_path());
        $packages = [];
        foreach ($composer->getRepositoryManager()->getRepositories() as $repository) {
            $searches = $repository->search('', 0, 'sunapp-module');
            foreach ($searches as $search) {
                $packages[$search['name']] = $search['name'];
            }
        }
        $package = [];
        foreach ($packages as $key => $value) {
            if (strpos($value, 'sunapp') === false || strpos($value, 'sunapp/platform') !== false) {
                continue;
            }
            $replace = preg_replace(['/sunapp/i', '/sungroup/i', '/\//i', '/_/i'], '', $value);
            if ($replace == '') {
                continue;
            }
            $package[$key]['realname'] = $value;
            $package[$key]['name'] = $replace;
        }
        $arr = $this->prepareCompserDataToShow($package);

        return $arr;
    }

    private function prepareCompserDataToShow($packages)
    {
        $package = [];
        foreach ($packages as $k => $v) {
            $package[$k]['installed'] = false;
            $package[$k]['status'] = false;
            $package[$k]['realname'] = $v['realname'];
            $package[$k]['name'] = $v['name'];
            foreach (Modules::getModuleStatuses() as $key => $value) {
                if ($v['name'] == strtolower($key)) {
                    $package[$k]['installed'] = true;
                    $package[$k]['status'] = $value;
                }
            }
        }
        return $package;
    }

    public function installModule(Request $request)
    {
        $data = $request->all();
        $status = false;

        if ($data['data']['obj']['name']) {
            $name = $data['data']['obj']['name'];
            $realname = $data['data']['obj']['realname'];
        }

        if ($data['data']['status'] == true) {
            $base_path = 'cd ' . base_path() . ' && composer require ' . $realname . ' --no-cache';
            $base_path .= ' && php artisan module:enable ' . $name;
            try {
                $process = new Process([$base_path]);
                $process->setTimeout(3600);

                $process->run(function ($type, $buffer) use ($realname) {
                    // \Log::debug('Instalowanie modulu '. $realname);
                    // \Log::debug($buffer);
                });

                $status = $process->getOutput();
            } catch (ProcessFailedException $exception) {
                \Log::error('Error: Instalowanie modulu');
                \Log::error($exception->getMessage());
            }
        } else {
            // odinstaluj moduł
            try {
                $status = \Artisan::call("module:delete $name");
            } catch (ProcessFailedException $exception) {
                \Log::error('Error: odinstalowywanie modulu');
                \Log::error($exception->getMessage());
            }
        }
        return $status;
    }
}
