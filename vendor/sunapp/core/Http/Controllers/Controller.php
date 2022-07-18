<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use SunApp\Http\Controllers\Controller as BaseController;
use SunAppModules\Core\Entities\NestedModel;
use SunAppModules\Core\src\FormBuilder\FormBuilderTrait;
use SunAppModules\Core\Entities\Modules;

class Controller extends BaseController
{
    use FormBuilderTrait;

    protected $repository = null;
    protected $itemForm = null;
    protected $item = null;
    protected $items = [];

    public function getRepository()
    {
        return $this->repository;
    }

    public function prepareForm()
    {
        $this->itemForm = $this->form($this->formClass, [
            'model' => $this->item
        ]);
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return Response|View|\View
     */
    public function index(Request $request)
    {
        if (!Bouncer::can('show', $this->class)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->paginate();
        }

        if ($request->fixTree == 'true') {
            $this->items->fixTree();
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

    /**
     * Show the form for creating a new resource.
     * @return Response|View|\View
     */
    public function create()
    {
        if (!Bouncer::can('create', $this->class)) {
            abort(403);
        }
        $this->prepareForm();

        return theme_view(
            $this->prefix . '.create',
            [
                'form' => $this->itemForm,
                'item' => $this->item
            ]
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
        $this->prepareForm();
        $this->itemForm->redirectIfNotValid();
        DB::beginTransaction();
        try {
            if ($this->item instanceof NestedModel) {
                if ($request->has('parent_id')) {
                    $parent = $this->item::find($request->get('parent_id'));
                } else {
                    $parent = null;
                }
                $this->item = $this->class::create($request->all(), $parent);
            } else {
                $this->item = $this->class::create($request->all());
            }
            if (method_exists($this->item, 'groups')) {
                if ($request->has('group')) {
                    $this->item->groups()->sync($request->get('group'));
                }
                if ($request->has('groups')) {
                    $this->item->groups()->sync($request->get('groups'));
                }
            }
            if (method_exists($this->item, 'categories')) {
                if ($request->has('categories')) {
                    $this->item->categories()->sync($request->get('categories'));
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.create_success'),
            200,
            $this->repository->parserResult($this->item->fresh())
        );
    }

    /**
     * Show the specified resource.
     * @param  int  $id
     * @param  Request  $request
     * @return Response|View|\View
     */
    public function show($id, Request $request)
    {
        if ($request->has('trashed') && $request->trashed == 'only') {
            $this->item = $this->item->withTrashed()->findOrFail($id);
        } else {
            $this->item = $this->item->findOrFail($id);
        }
        if (!Bouncer::can('show', $this->item)) {
            abort(403);
        }
        if ($request->ajax()) {
            return $this->repository->find($id);
        }
        if ($request->has('logs')) {
            return $this->logs($id);
        }
        $this->prepareForm();
        $this->itemForm->setupModel($this->item);
        $this->itemForm->disableFields();

        return theme_view(
            $this->prefix . '.show',
            [
                'form' => $this->itemForm,
                'item' => $this->item
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @param  Request  $request
     * @return Response|View|\View
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
        $this->prepareForm();
        $this->itemForm->setupModel($this->item);

        return theme_view(
            $this->prefix . '.edit',
            [
                'form' => $this->itemForm,
                'item' => $this->item
            ]
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
        if (method_exists($this->class, 'withTrashed') || method_exists($this->class, 'restore')) {
            $this->item = $this->class::withTrashed()->findOrFail($id);
        } else {
            $this->item = $this->class::findOrFail($id);
        }
        if (!Bouncer::can('edit', $this->item)) {
            abort(403);
        }
        if ($request->get('restore') == true) {
            DB::beginTransaction();
            try {
                $this->item->restore();
                if ($this->item instanceof NestedModel) {
                    if ($this->item->parent == null && $this->item->parent_id != null) {
                        $this->item->makeRoot()->save();
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;
            }

            return redirect()->back()->withMessage(
                'success',
                trans('core::actions.restore_success'),
                200,
                $this->repository->parserResult($this->item)
            );
        }
        $this->item = $this->class::findOrFail($id);
        $this->prepareForm();
        $this->itemForm->setupModel($this->item);
        DB::beginTransaction();
        try {
            if ($request->has('items_ordering')) {
                $order = $ids = [];
                foreach ($request->get('items_ordering') as $k => $ordering_item) {
                    $order[] = ['order' => $k + 1];
                    $ids[] = $ordering_item;
                }
                $this->item->categoriables()->sync(array_combine($ids, $order));
                DB::commit();
                return redirect()->back()->withMessage(
                    'success',
                    trans('core::actions.reordering_success'),
                    200,
                    $this->repository->parserResult($this->item->fresh())
                );
            }
            if (!$request->has('moved')) {
                $this->itemForm->redirectIfNotValid();
                $this->item->update($request->all());
            }
            if ($this->item instanceof NestedModel) {
                if ($request->has('prev_id') || $request->has('next_id')) {
                    if ($request->get('prev_id') != null) {
                        $prev = $this->class::find($request->get('prev_id'));
                        $this->item->insertAfterNode($prev);
                    }
                    if ($request->get('next_id') != null) {
                        $next = $this->class::find($request->get('next_id'));
                        $this->item->insertBeforeNode($next);
                    }
                }
                if (
                    $request->has('parent_id') &&
                    $this->item->parent_id != $request->get('parent_id')
                ) {
                    if ($request->get('parent_id') == null) {
                        $this->item->makeRoot()->save();
                    } else {
                        $parent = $this->class::find($request->get('parent_id'));
                        $parent->appendNode($this->item);
                    }
                }
            } else {
                if ($request->has('items')) {
                    foreach ($request->items as $k => $item) {
                        $this->class::where('id', $item)->update(['order' => $k + 1]);
                    }
                }
            }
            if (method_exists($this->item, 'groups')) {
                if ($request->has('group')) {
                    $this->item->groups()->sync($request->get('group'));
                }
                if ($request->has('groups')) {
                    $this->item->groups()->sync($request->get('groups'));
                }
            }
            if (method_exists($this->item, 'categories')) {
                if ($request->has('categories')) {
                    $this->item->categories()->sync($request->get('categories'));
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.update_success'),
            200,
            $this->repository->parserResult($this->item->fresh())
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
        if (method_exists($this->class, 'trashed')) {
            $this->item = $this->class::withTrashed()->findOrFail($id);
        } else {
            $this->item = $this->class::findOrFail($id);
        }
        if (!Bouncer::can('destroy', $this->item)) {
            abort(403);
        }
        if ($request->get('force', 0) == true) {
            DB::beginTransaction();
            try {
                if (method_exists($this->item, 'languages')) {
                    $this->item->languages()->detach();
                }
                $this->item->forceDelete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;
            }

            return redirect()->back()->withMessage('success', trans('core::actions.force_destroy_success'));
        }
        DB::beginTransaction();
        try {
            if ($this->item instanceof NestedModel) {
                foreach ($this->item->children as $child) {
                    $child->makeRoot()->save();
                }
                $this->item->makeRoot()->save();
                $this->class::fixTree();
            }
            $this->item->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return redirect()->back()->withMessage('success', trans('core::actions.destroy_success'));
    }

    /**
     * Changes activity of the specified resource from storage.
     * @param  int  $id
     * @param  Request  $request
     * @return Response
     * @throws Exception
     */
    public function activity($id, Request $request)
    {
        $this->item = $this->item->findOrFail($id);
        if (!Bouncer::can('edit', $this->item)) {
            abort(403);
        }
        DB::beginTransaction();
        try {
            $this->item->update($request->all());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return redirect()->back()->withMessage('success', trans('core::messages.activity_changed'));
    }

    public function logs($id)
    {
        $sessionData[] = [
            'auditable_model' => $this->class,
            'auditable_id' => $id,
        ];
        session(['auditable_data' => $sessionData]);
        if (strpos(request()->url(), '/sg-admin')) {
            $url = '/sg-admin/update-history/element';
        } else {
            $url = '/update-history/element';
        }
        return redirect($url);
    }
}
