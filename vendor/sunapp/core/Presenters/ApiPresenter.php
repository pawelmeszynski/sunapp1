<?php

namespace SunAppModules\Core\Presenters;

use Bouncer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\AbstractPaginator;
use Prettus\Repository\Presenter\FractalPresenter;
use SunAppModules\Core\Transformers\ApiTransformer;

class ApiPresenter extends FractalPresenter
{
    protected $links = [];
    protected $params = [];

    protected $resourceKeyItem;
    protected $resourceKeyCollection;

    /**
     * Prepare data to present
     *
     * @return FrontTransformer
     */
    public function getTransformer()
    {
        return new ApiTransformer();
    }

    /**
     * Prepare data to present
     *
     * @param $data
     *
     * @return mixed
     * @throws Exception
     */
    public function present($data)
    {
        if ($data instanceof EloquentCollection || $data instanceof AbstractPaginator) {
            if (request()->route()->controller && method_exists(request()->route()->controller, 'getRepository')) {
                $model = request()->route()->controller->getRepository()
                    ? request()->route()->controller->getRepository()->model() : null;
                if ($model) {
                    $model = new $model();
                    $this->resourceKeyItem = $this->resourceKeyCollection = get_class($model);
                }
            }
        } elseif ($data && $data->namespace) {
            $this->resourceKeyItem = $this->resourceKeyCollection = get_class($data);
        }
        $presented = parent::present($data);
        return $presented;
    }
}
