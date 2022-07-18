<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Module;
use Repository;
use SunAppModules\Core\Entities\Role;
use SunAppModules\Core\Forms\RoleForm;
use View;

class RolesController extends Controller
{
    protected $prefix;
    protected $class;
    protected $repository;
    protected $item;
    protected $items;
    protected $itemForm;
    protected $formClass;

    /**
     * Controller constructor.
     * @param  Repository  $repository
     */
    public function __construct(Repository $repository)
    {
        $this->prefix = 'core::roles';
        $this->class = Role::class;
        $this->repository = $repository;
        $this->repository->setModel(Role::class)->setSearchable([
            'name' => 'like',
            'title' => 'like'
        ]);
        $this->item = new Role();
        $this->items = Role::query();
        $this->itemForm = $this->form(RoleForm::class, [
            'model' => $this->item
        ]);
        $this->formClass = RoleForm::class;
    }

    /**
     * Display a listing of the resource.
     * @return Response|View
     */
    public function index(Request $request)
    {
        if (!Bouncer::can('show', $this->class)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->paginate();
        }
        $abilities = [];
        foreach (Module::all() as $name => $module) {
            $module_json = $module->json();
            if ($module_json->get('abilities')) {
                $abilities[$name] = [
                    'name' => $module_json->get('translation') ?? $module->getName(),
                    'items' => $module_json->get('abilities')
                ];
            }
        }
        return theme_view(
            $this->prefix . '.index',
            ['items' => $this->items, 'form' => $this->itemForm, 'abilities' => $abilities]
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Response|View
     */
    public function create()
    {
        if (!Bouncer::can('create', $this->class)) {
            abort(403);
        }
        $abilities = [];
        foreach (Module::all() as $name => $module) {
            $module_json = $module->json();
            if ($module_json->get('abilities')) {
                $abilities[$name] = [
                    'name' => $module_json->get('translation') ?? $module->getName(),
                    'items' => $module_json->get('abilities')
                ];
            }
        }
        return theme_view(
            $this->prefix . '.create',
            ['form' => $this->itemForm, 'item' => $this->item, 'abilities' => $abilities]
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Bouncer::can('create', $this->class)) {
            abort(403);
        }
        $this->itemForm->redirectIfNotValid();
        $item = $this->item->firstOrCreate([
            'name' => $request->name,
            'title' => $request->title,
        ]);
        $item->abilities()->sync([]);
        if ($request->has('abilities')) {
            foreach ($request->abilities as $module => $permissions) {
                $obj = new $module();
                $class = get_class($obj);
                foreach ($permissions as $permission => $v) {
                    Bouncer::allow($item)->to($permission, $class);
                }
            }
        }
        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.create_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    /**
     * Show the specified resource.
     * @param  int  $id
     * @return Response|View
     */
    public function show($id, Request $request)
    {
        $this->item = $this->item->findOrFail($id);
        if (!Bouncer::can('show', $this->item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        $abilities = [];
        foreach (Module::all() as $name => $module) {
            $module_json = $module->json();
            if ($module_json->get('abilities')) {
                $abilities[$name] = [
                    'name' => $module_json->get('translation') ?? $module->getName(),
                    'items' => $module_json->get('abilities')
                ];
            }
        }
        $this->prepareForm();
        $this->itemForm->setupModel($this->item);
        return theme_view(
            $this->prefix . '.show',
            ['form' => $this->itemForm, 'item' => $this->item, 'abilities' => $abilities]
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @return Response|View
     */
    public function edit($id, Request $request)
    {
        $this->item = $this->item->findOrFail($id);
        if (!Bouncer::can('edit', $this->item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        $abilities = [];
        foreach (Module::all() as $name => $module) {
            $module_json = $module->json();
            if ($module_json->get('abilities')) {
                $abilities[$name] = [
                    'name' => $module_json->get('translation') ?? $module->getName(),
                    'items' => $module_json->get('abilities')
                ];
            }
        }
        $this->prepareForm();
        $this->itemForm->setupModel($this->item);
        return theme_view(
            $this->prefix . '.edit',
            ['form' => $this->itemForm, 'item' => $this->item, 'abilities' => $abilities]
        );
    }

    /**
     * Update the specified resource in storage.
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $item = Role::findOrFail($id);
        if (!Bouncer::can('edit', $item)) {
            abort(403);
        }
        $this->itemForm->setupModel($item);
        $this->itemForm->redirectIfNotValid();
        $item->update([
            'name' => $request->name,
            'title' => $request->title,
        ]);
        $request = $this->addRequiredAbilites($request);
        $item->abilities()->sync([]);
        if ($request->has('abilities')) {
            foreach ($request->abilities as $module => $permissions) {
                $obj = new $module();
                $class = get_class($obj);
                foreach ($permissions as $permission => $v) {
                    Bouncer::allow($item)->to($permission, $class);
                }
            }
        }

        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.update_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, Request $request)
    {
        $item = $this->item->findOrFail($id);
        if (!Bouncer::can('destroy', $item)) {
            abort(403);
        }
        $item->delete();
        return redirect()->back()->withMessage('success', trans('core::actions.destroy_success'));
    }

    public function addRequiredAbilites($request)
    {
        $abilities = $request->abilities;
        if ($abilities) {
            foreach (Module::all() as $name => $module) {
                $module_json = $module->json();
                if ($module_json->has('abilities_reqiures')) {
                    $abilities_requries = $module_json->get('abilities_reqiures');
                    foreach ($abilities as $ability => $permissions) {
                        foreach ($abilities_requries as $required_ability => $required_permissions) {
                            if ($ability == $required_ability) {
                                foreach ($permissions as $permission => $status) {
                                    if (isset($required_permissions[$permission]) && $status == '1') {
                                        foreach ($required_permissions[$permission] as $model => $action) {
                                            $abilities[$model][$action] = '1';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $request->request->add(['abilities' => $abilities]);
        }
        return $request;
    }

    public function showReqiredAbilities(Request $request)
    {
        $model = $request->model;
        $action = $request->action;
        if ($model && $action) {
            $moduleName = explode('\\', $model)[2];
            $module = Module::find($moduleName);
            $module_json = $module->json();
            if ($module_json->has('abilities_reqiures')) {
                $abilities_requries = $module_json->get('abilities_reqiures');
                if (isset($abilities_requries[$model]) && isset($abilities_requries[$model][$action])) {
                    foreach ($abilities_requries[$model][$action] as $model => $permission) {
                        $required_abilities['data'][] = [
                            'model' => $model,
                            'ability' => $permission
                        ];
                    }
                } elseif (isset($abilities_requries[$model]) && $action == '*') {
                    foreach ($abilities_requries[$model] as $permissions) {
                        foreach ($permissions as $model => $permission) {
                            $required_abilities['data'][] = [
                                'model' => $model,
                                'ability' => $permission
                            ];
                        }
                    }
                }
            }
        }
        if (!isset($required_abilities)) {
            $required_abilities = null;
        }
        return $required_abilities;
    }
}
