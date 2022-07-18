<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Transformers;

use League\Fractal\TransformerAbstract;
use User;

class ByUserTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];
    protected $availableIncludes = [];

    public function transform(User $item)
    {
        return array_merge($item->toArray(), [
            'links' => $item->actions,
            'type' => get_class($item)
        ]);
    }
}
