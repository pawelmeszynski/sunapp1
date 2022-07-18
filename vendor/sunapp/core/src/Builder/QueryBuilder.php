<?php

namespace SunAppModules\Core\src\Builder;

use Illuminate\Database\Eloquent\Builder;

class QueryBuilder extends Builder
{
    /**
     * Order by default column.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function defaultOrder($dir = 'asc')
    {
        $this->query->orders = null;

        $this->query->orderBy($this->model->getDefaultOrderColumn(), $dir);

        return $this;
    }

    public function getModels($columns = ['*'])
    {
        if ($this->getQuery()->orders === null) {
            $this->defaultOrder();
        }
        return parent::getModels($columns);
    }

    /**
     * Order by reversed default column.
     *
     * @return $this
     */
    public function reversed()
    {
        return $this->defaultOrder('desc');
    }
}
