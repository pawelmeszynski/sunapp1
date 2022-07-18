<?php

namespace SunAppModules\Core\Transformers;

use League\Fractal\TransformerAbstract;

class ApiTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];
    protected $availableIncludes = [];

    public function transform($item)
    {
        return array_merge(
            $item->toArray(),
            [
                'links' => $item->api_actions,
            ]
        );
    }
}
