<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Entities;

use SunAppModules\Core\src\Nestedset\NodeTrait;

class NestedModel extends Model
{
    use NodeTrait;

    protected $fillable = [];

    public function getNestedNameAttribute()
    {
        //$depth = $this->withTrashed()->withDepth()->where('id', $this->id)->first('depth')->depth;
        $depth = $this->depth;
        if ($depth < 0) {
            $depth = 0;
        }
        return str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . $this->name;
    }

    public function getChildrenCountAttribute()
    {
        return $this->children()->count();
    }
}
