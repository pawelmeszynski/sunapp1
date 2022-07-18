<?php

namespace SunAppModules\Core\Entities;

use SunAppModules\Core\src\Builder\QueryBuilder;

class SecurityLocks extends Model
{
    protected $fillable = ['id', 'active', 'blocked', 'ip_address', 'blocked_from', 'blocked_to'];
    protected $actions = ['index', 'show', 'edit', 'update' => 'edit'];
    protected $namespace = 'core::locks';
    protected $searchable = [
        'active' => '=',
        'blocked' => '=',
        'ip_address' => 'like',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'blocked_from' => 'datetime',
        'blocked_to' => 'datetime'
    ];

    public function isIPLocked($ip)
    {
        $lock = SecurityLocks::where('ip_address', $ip)
            ->where('active', 1)
            ->where('blocked', 1)
            ->orderBy('created_at', 'desc')
            ->first();
        return $lock ? true : false;
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

    public function getActiveAttribute($value)
    {
        if (request()->ajax() && request()->route()->getName() == 'SunApp::core.locks.show') {
            return trans('core::security.' . ($value ? 'yes' : 'no'));
        }
        return $value;
    }

    public function getBlockedAttribute($value)
    {
        if (request()->ajax() && request()->route()->getName() == 'SunApp::core.locks.show') {
            return trans('core::security.' . ($value ? 'yes' : 'no'));
        }
        return $value;
    }
}
