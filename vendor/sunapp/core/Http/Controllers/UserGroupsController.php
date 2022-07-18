<?php

namespace SunAppModules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Repository;
use SunAppModules\Core\Forms\UserGroupForm;
use SunAppModules\Core\Entities\UserGroup;
use View;

class UserGroupsController extends Controller
{
    protected $prefix = 'core::groups';
    protected $class = UserGroup::class;
    protected $formClass = UserGroupForm::class;

    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();

        $this->repository = $repository;
        $this->repository->setModel(UserGroup::class)->setSearchable([
            'name' => 'like'
        ]);
    }

    //
    
    /**
     * Store a newly created resource in storage.
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $item = new UserGroup();
        $form = $this->form(UserGroupForm::class, [
            'model' => $item
        ]);
        $this->prepareForm();
        $this->itemForm->redirectIfNotValid();
        if ($request->has('parent_id')) {
            $parent = UserGroup::find($request->get('parent_id'));
        } else {
            $parent = null;
        }
        $item = UserGroup::create($request->all(), $parent);

        return redirect()->back()->withMessage(
            'success',
            trans('core::actions.create_success'),
            200,
            $this->repository->parserResult($item->fresh())
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @return Response|View
     */
    public function edit($id, Request $request)
    {
        if ($request->ajax()) {
            $item = $this->repository->find($id);
            if ($item['data']['attributes']['core'] === 1) {
                abort(403, trans('core::messages.cant_edit_core_group'));
            }
            return $item;
        }
        $item = UserGroup::find($id);
        if ($item->core === 1) {
            return redirect(route('SunApp::core.groups.index'))->withMessage(
                'error',
                trans('core::messages.cant_edit_core_group'),
                403,
                $this->repository->parserResult($item->fresh())
            );
        }
        $this->itemForm->setupModel($item);
        return theme_view('core::groups.edit', ['form' => $this->itemForm, 'item' => $item]);
    }

    /**
     * Update the specified resource in storage.
     * @param  int  $id
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if ($request->get('restore') == true) {
            $item = UserGroup::withTrashed()->find($id);
            $item->restore();
            if ($item->parent == null && $item->parent_id != null) {
                $item->makeRoot()->save();
            }
            return redirect()->back()->withMessage(
                'success',
                trans('core::actions.restore_success'),
                200,
                $this->repository->parserResult($item)
            );
        }
        $item = UserGroup::find($id);
        $this->itemForm = $this->form(UserGroupForm::class, [
            'model' => $item
        ]);
        if ($item->core === 1) {
            abort(403, trans('core::messages.cant_edit_core_group'));
        }
        if (!$request->has('moved')) {
            $this->itemForm->redirectIfNotValid();
            $item->update($request->all());
        }
        if ($this->itemForm) {
            $this->itemForm->setupModel($item);
        }
        if ($request->has('prev_id') || $request->has('next_id')) {
            if ($request->get('prev_id') != null) {
                $prev = UserGroup::find($request->get('prev_id'));
                $item->insertAfterNode($prev);
            }
            if ($request->get('next_id') != null) {
                $next = UserGroup::find($request->get('next_id'));
                $item->insertBeforeNode($next);
            }
        }
        if ($request->has('parent_id') && $item->parent_id != $request->get('parent_id')) {
            if ($request->get('parent_id') == null) {
                $item->makeRoot()->save();
            } else {
                $parent = UserGroup::find($request->get('parent_id'));
                $parent->appendNode($item);
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
        if ($request->get('force', 0) == true) {
            $item = UserGroup::withTrashed()->find($id);
            $item->forceDelete();
            return redirect()->back()->withMessage('success', trans('core::actions.force_destroy_success'));
        }
        $item = UserGroup::find($id);
        if ($item->core === 1) {
            abort(403, trans('core::messages.cant_remove_core_group'));
        }
        foreach ($this->item->children as $child) {
            $child->makeRoot()->save();
        }
        $item->makeRoot()->save();
        $item->delete();
        UserGroup::fixTree();
        return redirect()->back()->withMessage('success', trans('core::actions.destroy_success'));
    }
}
