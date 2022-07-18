<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Entities;

use Bouncer;
use Illuminate\Database\Eloquent\Model as DefaultModel;
use Illuminate\Support\Facades\Route;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use SunAppModules\Core\src\Builder\QueryBuilder;
use SunAppModules\Core\Traits\ExtraFields;
use SunAppModules\Core\Traits\MacroableModel;

class Model extends DefaultModel implements AuditableContract
{
    use Auditable;
    use MacroableModel;
    use ExtraFields;

    protected $searchable = [];
    protected $fillable = [];
    protected $casts = [];
    protected $with = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $actions = ['show', 'edit', 'update' => 'edit', 'destroy'];
    protected $trashedActions = ['show' => '?trashed=only', 'update' => '?restore=true', 'destroy' => '?force=true'];

    protected $defaults_appends = ['createdBy', 'updatedBy', 'deletedBy'];
    protected $default_order_column = 'id';
    protected $namespace = null;

    public function __construct(array $attributes = [])
    {
        if (!config('system.front')) {
            $this->appends = array_merge($this->appends, $this->defaults_appends);
        }
        $this->appends = array_merge($this->appends, config('installed_appends', []));
        $this->appends = array_merge($this->appends, config('installed_model_appends.' . get_class($this), []));
        Bouncer::ownedVia(get_called_class(), function ($item, $user) {
            if ($createdBy = $this->createdBy()) {
                return $createdBy->id == $user->id;
            }
            return false;
        });
        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            foreach ($model->attributes as $attribute => $value) {
                if (str_contains($attribute, 'cached_')) {
                    unset($model->{$attribute});
                }
            }
        });
    }

    public function createdBy()
    {
        $audit = $this->audits()->with('user')->where('event', 'created')->latest()->first();
        return $audit ? $audit->user : false;
    }

    public function updatedBy()
    {
        $audit = $this->audits()->with('user')->latest()->first();
        return $audit ? $audit->user : false;
    }

    public function deletedBy()
    {
        $audit = $this->audits()->with('user')->where('event', 'deleted')->latest()->first();
        return $audit ? $audit->user : false;
    }

    public function getCreatedByAttribute()
    {
        return ($createdBy = $this->createdBy()) ? $createdBy->name : '';
    }

    public function getUpdatedByAttribute()
    {
        return ($updatedBy = $this->updatedBy()) ? $updatedBy->name : '';
    }

    public function getDeletedByAttribute()
    {
        return ($deletedBy = $this->deletedBy()) ? $deletedBy->name : '';
    }

    public function scopeOnlyActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeOnlyInactive($query)
    {
        return $query->where('active', 0);
    }

    public function getActionsAttribute()
    {

        $actions = [];
        if ($this->namespace) {
            if (
                !method_exists($this, 'trashed')
                || (method_exists($this, 'trashed') && !$this->trashed())
            ) {
                foreach ($this->actions as $action => $permission) {
                    if (!is_string($action)) {
                        $action = $permission;
                    }
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (\Bouncer::can($permission, $this)) {
                        $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this);
                    }
                }
            } else {
                foreach ($this->trashedActions as $action => $params) {
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (\Bouncer::can($action, $this)) {
                        $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this) . $params;
                    }
                }
            }
        }
        return $actions;
    }

    public function getApiActionsAttribute()
    {
        $actions = [];
        if ($this->namespace) {
            if (
                !method_exists($this, 'trashed')
                || (method_exists($this, 'trashed') && !$this->trashed())
            ) {
                foreach ($this->actions as $action => $permission) {
                    if (!is_string($action)) {
                        $action = $permission;
                    }
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (Route::has("SunAppApi::{$routeNamespace}.{$action}")) {
                        if (\Bouncer::can($permission, $this)) {
                            $actions[$action] = route("SunAppApi::{$routeNamespace}.{$action}", $this);
                        }
                    }
                }
            } else {
                foreach ($this->trashedActions as $action => $params) {
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (Route::has("SunAppApi::{$routeNamespace}.{$action}", $this)) {
                        if (\Bouncer::can($action, $this)) {
                            $actions[$action] = route("SunAppApi::{$routeNamespace}.{$action}", $this) . $params;
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

    /**
     * Encode the given value as JSON.
     *
     * @param mixed $value
     * @return string
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_NUMERIC_CHECK);
    }

    public function getDefaultOrderColumn()
    {
        return $this->default_order_column;
    }

    public function newEloquentBuilder($query)
    {
        return new QueryBuilder($query);
    }

    public function addFillable($fields)
    {
        $this->fillable = array_merge($this->fillable, $fields);
    }

    public function addCasts($fields)
    {
        $this->casts = array_merge($this->casts, $fields);
    }
}
