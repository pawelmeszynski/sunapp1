<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class TrashedCriteria.
 */
class TrashedCriteria implements CriteriaInterface
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param  string  $model
     * @param  RepositoryInterface  $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $withThrashed = $this->request->get(
            config('repository.criteria.params.trashed', 'trashed'),
            null
        );
        switch ($withThrashed) {
            case 'with':
                $model = $model->withTrashed();
                break;
            case 'only':
                $model = $model->onlyTrashed();
                break;
        }
        return $model;
    }
}
