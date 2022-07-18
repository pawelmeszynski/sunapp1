<?php

namespace SunAppModules\Core\Entities;

use SunAppModules\Core\src\Builder\QueryBuilder;

class SecurityExceptions extends Model
{
    protected $fillable = ['status_code', 'exception_type', 'ip_address', 'url', 'message', 'method', 'user_agent'];
    protected $actions = ['index', 'show', 'edit', 'update' => 'edit'];
    protected $namespace = 'core::sec-exceptions';
    protected $searchable = [
        'status_code' => 'like',
        'ip_address' => 'like',
    ];
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'message'   => 'json',
    ];

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
     * @param  mixed  $value
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

    public function getMessageAttribute($value)
    {
        if (request()->json() && request()->route()->getName() == 'SunApp::core.sec-exceptions.show') {
            return json_encode($value);
        }
        return $value;
    }
}
