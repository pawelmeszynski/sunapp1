<?php

namespace SunAppModules\Core\Http\Controllers;

use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\Core\Repositories\Repository;
use SunAppModules\Core\src\FormBuilder\Form;
use SunAppModules\Core\Entities\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Bouncer;

class AuditsController extends Controller
{
    protected $prefix = 'core::audits';
    protected $class = Audit::class;
    protected $formClass = Form::class;

    /**
     * Controller constructor.
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->item = new $this->class();
        $this->items = $this->class::query();
        $this->repository = $repository;

        $this->repository->setModel($this->class)->setSearchable([
            //
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
            return $this->repository->orderBy('id', 'desc')->paginate();
        }

        return theme_view(
            $this->prefix . '.index',
            [
                'items' => $this->items,
                'form' => $this->itemForm,
                'item' => $this->item
            ]
        );
    }

    public function element(Request $request)
    {
        if (!Bouncer::can('show', $this->class)) {
            abort(403);
        }
        if ($request->ajax()) {
            if ($sessionData = session('auditable_data')) {
                return $this->repository->scopeQuery(function ($query) use ($sessionData) {
                    $mainModel = $sessionData[0];
                    unset($sessionData[0]);
                    $query->where([
                        'auditable_id' => $mainModel['auditable_id'],
                        'auditable_type' => $mainModel['auditable_model'],
                    ]);
                    foreach ($sessionData as $subModel) {
                        $query->orWhere(function ($subquery) use ($subModel) {
                            $subquery->where([
                                'auditable_id' => $subModel['auditable_id'],
                                'auditable_type' => $subModel['auditable_model'],
                            ]);
                        });
                    }
                    return $query;
                })->orderBy('id', 'desc')->paginate();
            }
            return $this->repository->orderBy('id', 'desc')->paginate();
        }

        return theme_view(
            $this->prefix . '.index',
            [
                'items' => $this->items,
                'form' => $this->itemForm,
                'item' => $this->item
            ]
        );
    }
}
