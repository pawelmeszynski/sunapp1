<?php

namespace SunAppModules\Core\src\Bouncer\Database;

use Silber\Bouncer\Database\Ability as BaseAbility;
use SunAppModules\Core\src\Bouncer\Database\Queries\AbilitiesForModel;

class Ability extends BaseAbility
{
    public static function makeForModel($model, $attributes)
    {
        if (is_string($attributes)) {
            $attributes = ['name' => $attributes];
        }

        if ($model === '*') {
            return (new static())->forceFill($attributes + [
                    'entity_type' => '*',
                ]);
        }

        if (is_string($model)) {
            $model = new $model();
        }

        if (method_exists($model, 'getAbilityMorphClass')) {
            return (new static())->forceFill($attributes + [
                    'entity_type' => $model->getAbilityMorphClass(),
                    'entity_id'   => $model->exists ? $model->getKey() : null,
                ]);
        }

        return (new static())->forceFill($attributes + [
                'entity_type' => $model->getMorphClass(),
                'entity_id'   => $model->exists ? $model->getKey() : null,
            ]);
    }

    /**
     * Constrain a query to an ability for a specific model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @param  bool  $strict
     */
    public function scopeForModel($query, $model, $strict = false)
    {
        (new AbilitiesForModel())->constrain($query, $model, $strict);
    }
}
