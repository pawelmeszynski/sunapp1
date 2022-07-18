<?php

namespace SunAppModules\Core\Entities;

use Bouncer;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Route;

class OfflineModel extends Model
{
    protected $client;
    protected $actions = ['show', 'edit', 'update' => 'edit', 'destroy'];
    protected $trashedActions = ['show' => '?trashed=only', 'update' => '?restore=true', 'destroy' => '?force=true'];
    protected $defaults_appends = ['createdBy', 'updatedBy', 'deletedBy'];
    protected $namespace = null;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function all($columns = ['*'])
    {
        return Collection::make([]);
    }

    public static function findOrFail($id)
    {
        $class_name = get_called_class();
        $item = $class_name::all()->find($id);
        if ($item) {
            return $item;
        }
        abort(404);
    }

    public static function find($id)
    {
        $class_name = get_called_class();
        return $class_name::all()->find($id);
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $results = ($total = $this->all()->count())
            ? $this->all()->forPage($page, $perPage)
            : $this->all();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return LengthAwarePaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    public function forPage($page, $perPage = 50)
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    public function getActionsAttribute()
    {
        $actions = [];
        if ($this->namespace) {
            if ((method_exists($this, 'trashed') && !$this->trashed()) || !method_exists($this, 'trashed')) {
                foreach ($this->actions as $action => $permission) {
                    if (!is_string($action)) {
                        $action = $permission;
                    }
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (Bouncer::can("{$this->namespace}.{$permission}", $this)) {
                        if (Route::has("SunApp::{$routeNamespace}.{$action}")) {
                            $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this);
                        }
                    }
                }
            } else {
                foreach ($this->trashedActions as $action => $params) {
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (Bouncer::can("{$this->namespace}.{$action}", $this)) {
                        if (Route::has("SunApp::{$routeNamespace}.{$action}")) {
                            $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this) . $params;
                        }
                    }
                }
            }
        }
        return $actions;
    }

    public function getNamespaceAttribute()
    {
        return $this->namespace;
    }

    public function getSearchableAttribute()
    {
        return $this->searchable ?? [];
    }
}
