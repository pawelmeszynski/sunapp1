<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SunAppModules\Core\Entities\ExtraField;
use SunAppModules\Core\Entities\ExtraFieldEntity;
use SunAppModules\Core\Entities\NestedModel;
use SunAppModules\Core\Forms\ExtraFieldForm;
use SunAppModules\Core\Repositories\Repository;
use View;

class ExtraFieldsController extends Controller
{
    protected $prefix = 'core::extra-fields';
    protected $class = ExtraField::class;
    protected $formClass = ExtraFieldForm::class;

    /**
     * Controller constructor.
     * @param  Repository  $repository
     */
    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;

        $this->repository->setModel($this->class)->setSearchable([
            'active',
            'default',
            'name' => 'like',
            'url' => 'like'
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
            return $this->repository->paginate();
        }
        $this->prepareForm();

        $entities = $this->getModulesEntities();

        return theme_view(
            $this->prefix . '.index',
            [
                'items' => $this->items,
                'form' => $this->itemForm,
                'item' => $this->item,
                'entities' => $entities
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Response|View|View
     */
    public function create()
    {
        if (!Bouncer::can('create', $this->class)) {
            abort(403);
        }
        $this->prepareForm();
        $entities = $this->getModulesEntities();
        return theme_view(
            $this->prefix . '.create',
            [
                'form' => $this->itemForm,
                'item' => $this->item,
                'entities' => $entities
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function store(Request $request)
    {
        if (!Bouncer::can('create', $this->class)) {
            abort(403);
        }
        $this->prepareForm();
        $this->itemForm->redirectIfNotValid();
        if (Schema::hasColumn('extra_field_values', Str::slug($request->name, '_'))) {
            return response()->json([
                'type' => 'error',
                'errors' => [
                    'name' => [
                        'Takie pole już istnieje'
                    ]
                ]
            ], 422);
        }
        $item = $this->class::create($request->all());
        Schema::table('extra_field_values', function (Blueprint $table) use ($item) {
            if ($item->translatable) {
                $table->json(Str::slug($item->name, '_'))->nullable()->after('entity_id');
            } else {
                $table->text(Str::slug($item->name, '_'))->nullable()->after('entity_id');
            }
        });

        if ($request->has('entities') && method_exists($this->item, 'entities')) {
            $entities = [];
            foreach ($request->entities as $entity) {
                $entities[] = new ExtraFieldEntity(['entity_type' => $entity]);
            }
            $item->entities()->delete();
            $item->entities()->saveMany($entities);
        }

        $item = $item->fresh();
        $item->touch();
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
     * @param  Request  $request
     * @return Response|View|View
     */
    public function show($id, Request $request)
    {
        $item = $this->item->findOrFail($id);
        if (!Bouncer::can('show', $item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        if ($request->has('logs')) {
            return $this->logs($id);
        }
        $this->prepareForm();
        $this->itemForm->setupModel($item);
        $this->itemForm->disableFields();
        $entities = $this->getModulesEntities();
        return theme_view($this->prefix . '.show', [
            'form' => $this->itemForm,
            'item' => $item,
            'entities' => $entities
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @param  Request  $request
     * @return Response|View|View
     */
    public function edit($id, Request $request)
    {
        $item = $this->item->findOrFail($id);
        if (!Bouncer::can('edit', $item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        $this->prepareForm();
        $this->itemForm->setupModel($item);
        $entities = $this->getModulesEntities();
        return theme_view($this->prefix . '.edit', [
            'form' => $this->itemForm,
            'item' => $item,
            'entities' => $entities
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse|Response
     */
    public function update(Request $request, $id)
    {
        $item = $this->class::withTrashed()->findOrFail($id);
        if (!Bouncer::can('edit', $item)) {
            abort(403);
        }
        if ($request->get('restore') == true) {
            $item->restore();
            if ($this->item instanceof NestedModel) {
                if ($item->parent == null && $item->parent_id != null) {
                    $item->makeRoot()->save();
                }
            }

            return redirect()->back()->withMessage(
                'success',
                trans('core::actions.restore_success'),
                200,
                $this->repository->parserResult($item)
            );
        }
        $item = $this->class::findOrFail($id);
        $this->prepareForm();
        $this->itemForm->setupModel($item);

        $this->itemForm->redirectIfNotValid();
        $old_name = $item->name;
        if ($old_name != $request->name) {
            if (Schema::hasColumn('extra_field_values', Str::slug($request->name, '_'))) {
                return response()->json([
                    'type' => 'error',
                    'errors' => [
                        'name' => [
                            'Takie pole już istnieje'
                        ]
                    ]
                ], 422);
            }
            Schema::table('extra_field_values', function (Blueprint $table) use ($old_name, $request, $item) {
                if ($item->translatable) {
                    $table->renameColumn(Str::slug($old_name, '_'), Str::slug($request->name, '_'));
                } else {
                    $table->renameColumn(Str::slug($old_name, '_'), Str::slug($request->name, '_'));
                }
            });
        }
        $item->update($request->all());

        if ($request->has('entities') && method_exists($this->item, 'entities')) {
            $entities = [];
            foreach ($request->entities as $entity) {
                $entities[] = new ExtraFieldEntity(['entity_type' => $entity]);
            }
            $item->entities()->delete();
            $item->entities()->saveMany($entities);
        }

        $item = $item->fresh();
        $item->touch();
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
     * @param  Request  $request
     * @return Response
     * @throws Exception
     */
    public function destroy($id, Request $request)
    {
        $item = $this->item->withTrashed()->findOrFail($id);
        if (!Bouncer::can('destroy', $item)) {
            abort(403);
        }
        if ($request->get('force', 0) == true) {
            if (method_exists($item, 'languages')) {
                $item->languages()->detach();
            }
            if (Schema::hasColumn('extra_field_values', Str::slug($item->name, '_'))) {
                Schema::table('extra_field_values', function (Blueprint $table) use ($item) {
                    $table->dropColumn(Str::slug($item->name, '_'));
                });
            }
            $item->forceDelete();

            return redirect()->back()->withMessage('success', trans('core::actions.force_destroy_success'));
        }
        $item->delete();

        return redirect()->back()->withMessage('success', trans('core::actions.destroy_success'));
    }

    private function getModulesEntities()
    {
        $entities = [];
        foreach (app('modules')->all() as $module) {
            $entities[$module->getName()] = $this->getEntities($module);
        }
        return $entities;
    }

    private function getEntities($module)
    {
        /** @var array $entities */
        $entities = [];

        /** @var File $allFiles */
        $allFiles = File::glob($module->getPath() . '/Entities/*.php');
        foreach ($allFiles as $entity) {
            $entities[pathinfo($entity, PATHINFO_FILENAME)] = 'SunAppModules\\' . $module->getName() . "\Entities\\"
                . pathinfo($entity, PATHINFO_FILENAME);
        }

        return $entities;
    }

    public function logs($id)
    {
        $sessionData[] = [
            'auditable_model' => $this->class,
            'auditable_id' => $id,
        ];
        $extraFieldEntity = ExtraFieldEntity::where('extra_field_id', $id)->first();
        $sessionData[] = [
            'auditable_model' => ExtraFieldEntity::class,
            'auditable_id' => $extraFieldEntity->id,
        ];
        session(['auditable_data' => $sessionData]);
        return redirect('/sg-admin/update-history/element');
    }
}
