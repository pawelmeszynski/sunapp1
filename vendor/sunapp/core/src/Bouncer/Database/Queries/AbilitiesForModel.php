<?php

namespace SunAppModules\Core\src\Bouncer\Database\Queries;

use Illuminate\Database\Eloquent\Model;
use Silber\Bouncer\Database\Queries\AbilitiesForModel as BaseAbilitiesForModel;

class AbilitiesForModel extends BaseAbilitiesForModel
{
    /**
     * Get the constraint for regular model abilities.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  bool  $strict
     * @return \Closure
     */
    protected function modelAbilityConstraint(Model $model, $strict)
    {
        if (method_exists($model, 'getAbilityMorphClass')) {
            return function ($query) use ($model, $strict) {
                $query->where("{$this->table}.entity_type", $model->getAbilityMorphClass());

                $query->where($this->abilitySubqueryConstraint($model, $strict));
            };
        }
        return function ($query) use ($model, $strict) {
            $query->where("{$this->table}.entity_type", $model->getMorphClass());

            $query->where($this->abilitySubqueryConstraint($model, $strict));
        };
    }
}
