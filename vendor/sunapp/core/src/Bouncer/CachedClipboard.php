<?php

namespace SunAppModules\Core\src\Bouncer;

use Illuminate\Database\Eloquent\Model;
use Silber\Bouncer\CachedClipboard as BaseCachedClipboard;

class CachedClipboard extends BaseCachedClipboard
{
    /**
     * Compile a list of ability identifiers that match the given model.
     *
     * @param  string  $ability
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return array
     */
    protected function compileModelAbilityIdentifiers($ability, $model)
    {
        if ($model === '*') {
            return ["{$ability}-*", '*-*'];
        }

        $model = $model instanceof Model ? $model : new $model();

        if (method_exists($model, 'getAbilityMorphClass')) {
            $type = $model->getAbilityMorphClass();
        } else {
            $type = $model->getMorphClass();
        }

        $abilities = [
            "{$ability}-{$type}",
            "{$ability}-*",
            "*-{$type}",
            '*-*',
        ];

        if ($model->exists) {
            $abilities[] = "{$ability}-{$type}-{$model->getKey()}";
            $abilities[] = "*-{$type}-{$model->getKey()}";
        }

        return $abilities;
    }
}
