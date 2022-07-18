<?php

namespace SunAppModules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $namespace = 'core::configs';
    protected $searchable = [
        'key',
        'value',
    ];
    protected $fillable = [
        'key',
        'value',
    ];
}
