<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Presenters;

use Bouncer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\AbstractPaginator;
use Prettus\Repository\Presenter\FractalPresenter;
use Route;
use SunAppModules\Shop\Entities\ShopProduct;
use Transformer;

class Presenter extends FractalPresenter
{
    protected $links = [];
    protected $params = [];

    protected $resourceKeyItem;
    protected $resourceKeyCollection;

    /**
     * Prepare data to present
     *
     * @return Transformer
     */
    public function getTransformer()
    {
        return new Transformer();
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
        $serializer = config('repository.fractal.serializer', 'League\\Fractal\\Serializer\\JsonApiSerializer');
        if (!request()->get('only_data_request', null)) {
            if ($data instanceof EloquentCollection || $data instanceof AbstractPaginator) {
                if (request()->route()->controller && method_exists(request()->route()->controller, 'getRepository')) {
                    $model = request()->route()->controller->getRepository()
                        ? request()->route()->controller->getRepository()->model() : null;
                    if ($model) {
                        $model = new $model();
                        if ($model->namespace) {
                            $this->resourceKeyItem = $this->resourceKeyCollection = get_class($model);

                            if (method_exists($model, 'getPresenterCollectionLinks')) {
                                $this->links = $model->getPresenterCollectionLinks();
                            } else {
                                $routeNamespace = str_replace('::', '.', $model->namespace);
                                $this->links['index'] = route("SunApp::{$routeNamespace}.index");
                                if (Bouncer::can('create', $model)) {
                                    if (Route::has("SunApp::{$routeNamespace}.create")) {
                                        $this->links['create'] = route("SunApp::{$routeNamespace}.create");
                                    }
                                    if (Route::has("SunApp::{$routeNamespace}.store")) {
                                        $this->links['store'] = route("SunApp::{$routeNamespace}.store");
                                    }
                                }
                            }
                            $this->fractal->setSerializer(new $serializer($this->links['index']));
                        }
                        if (
                            !(App('request')->has('search') && $model instanceof ShopProduct)
                            && method_exists($model, 'getMetaParams')
                        ) {
                            $this->params = $model->getMetaParams();
                        }
                    }
                }
            } elseif ($data && $data->namespace) {
                if (method_exists($data, 'getPresenterItemLinks')) {
                    $this->links = $data->getPresenterItemLinks();
                } else {
                    $routeNamespace = str_replace('::', '.', $data->namespace);
                    $this->links['index'] = route("SunApp::{$routeNamespace}.index");
                }
                $this->resourceKeyItem = $this->resourceKeyCollection = get_class($data);
                $this->fractal->setSerializer(new $serializer($this->links['index']));
            }
        }
        $presented = parent::present($data);
        if (count($this->params)) {
            $presented['meta']['params'] = array_merge($presented['meta']['params'] ?? [], $this->params);
        }
        if (count($this->links)) {
            $presented['links'] = array_merge($presented['links'] ?? [], $this->links);
        }
        return $presented;
    }
}
