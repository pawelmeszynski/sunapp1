<?php

namespace SunAppModules\Core\src\Nestedset;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    /**
     * Fixes the tree based on parentage info.
     *
     * Nodes with invalid parent are saved as roots.
     *
     * @param NodeTrait|Model|null $root
     *
     * @return int The number of changed nodes
     */
    public function fixTree($root = null)
    {
        $columns = [
            $this->model->getKeyName(),
            $this->model->getParentIdName(),
            $this->model->getLftName(),
            $this->model->getRgtName(),
            $this->model->getDepthName(),
        ];

        $items = $this->model
            ->newNestedSetQuery()
            ->when($root, function (self $query) use ($root) {
                return $query->whereDescendantOf($root);
            })
            ->defaultOrder()
            ->withoutGlobalScopes()
            ->get($columns);

        foreach ($items as $model) {
            $model->save();
        }

        $dictionary = $this->model
            ->newNestedSetQuery()
            ->when($root, function (self $query) use ($root) {
                return $query->whereDescendantOf($root);
            })
            ->defaultOrder()
            ->withoutGlobalScopes()
            ->get($columns)
            ->groupBy($this->model->getParentIdName())
            ->all();
        return $this->fixNodes($dictionary, $root);
    }

    public function getModels($columns = ['*'])
    {
        if ($this->getQuery()->orders === null) {
            $this->defaultOrder();
        }
        return parent::getModels($columns);
    }
}
