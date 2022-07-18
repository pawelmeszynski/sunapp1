<?php

namespace SunAppModules\Core\Http\Controllers;

use Bouncer;
use Repository;
use Illuminate\Http\Request;
//use Nwidart\Modules\Json;
use SunAppModules\Core\Entities\Log;

class LogController extends Controller
{
    protected $prefix = "core::logs";
    protected $class = Log::class;
    protected $item;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->item = new $this->class();
        $this->repository->setModel($this->item);
    }

    public function index(Request $request)
    {
        if (!Bouncer::can('show', $this->class)) {
            abort(403);
        }

        if ($request->ajax()) {
            return $this->repository->paginate();
        }

        return theme_view(
            $this->prefix . '.index',
            [
                'item' => $this->item
            ]
        );
    }

    public function show($id, Request $request)
    {
        $this->item = $this->item->findOrFail($id);

        if (!Bouncer::can('show', $this->item)) {
            abort(403);
        }

        if ($request->ajax()) {
            return $this->repository->find($id);
        }

        return theme_view(
            $this->prefix . '.show',
            [
                'item' => $this->item
            ]
        );
    }
}
