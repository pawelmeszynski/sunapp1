<?php

namespace SunAppModules\Core\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

class ExtraFieldEntity extends Model
{
    use SoftDeletes;

    protected $fillable = ['extra_field_id', 'entity_type', 'entity_id'];
    protected $namespace = 'core::extra-fields';
}
