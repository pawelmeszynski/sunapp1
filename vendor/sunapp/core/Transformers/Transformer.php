<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Transformers;

use League\Fractal\TransformerAbstract;

class Transformer extends TransformerAbstract
{
    protected $defaultIncludes = [];
    protected $availableIncludes = ['createdBy', 'updatedBy', 'deletedBy'];

    public function transform($item)
    {
        return array_merge($item->toArray(), [
            'links' => $item->actions,
            //'type' => get_class($item),
        ]);
    }

    /**
     * Include CreatedBy
     */
    public function includeCreatedBy($item)
    {
        if (method_exists($item, 'createdBy')) {
            $user = $item->createdBy();
            if ($user) {
                return $this->item($user, new ByUserTransformer());
            }
        }
        return false;
    }

    /**
     * Include CreatedBy
     */
    public function includeUpdatedBy($item)
    {
        if (method_exists($item, 'updatedBy')) {
            $user = $item->updatedBy();
            if ($user) {
                return $this->item($user, new ByUserTransformer());
            }
        }
        return false;
    }

    /**
     * Include CreatedBy
     */
    public function includeDeletedBy($item)
    {
        if (method_exists($item, 'deletedBy')) {
            $user = $item->deletedBy();
            if ($user) {
                return $this->item($user, new ByUserTransformer());
            }
        }
        return false;
    }
}
