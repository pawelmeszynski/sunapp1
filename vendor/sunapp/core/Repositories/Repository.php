<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Repositories;

use Presenter;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;
use Silber\Bouncer\Database\Role;
use SunAppModules\Core\Entities\Model;
use SunAppModules\Core\Entities\NestedModel;
use SunAppModules\Core\Entities\OfflineModel;
use SunAppModules\Core\Entities\User;

class Repository extends BaseRepository // implements CacheableInterface
{
    protected $temp_model = Model::class;

    protected $fieldSearchable = [
        'name',
        'created_at',
    ];

    protected $fieldFilterable = [];

    public function boot()
    {
        $this->setPresenter(Presenter::class);
        $this->pushCriteria(app('SunAppModules\Core\Criteria\RequestCriteria'));
        $this->pushCriteria(app('SunAppModules\Core\Criteria\TrashedCriteria'));
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return $this->temp_model;
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param  array  $columns
     *
     * @return mixed
     */
    public function findOrFail($id, $columns = ['*'])
    {
        return $this->find($id, $columns);
    }

    /**
     * Set Model class name
     *
     * @return string
     */
    public function setModel($model = Model::class)
    {
        if (is_string($model)) {
            $this->temp_model = $model;
            $this->makeModel();
        } else {
            $this->model = $model;
            $this->temp_model = get_class($model);
        }
        if (method_exists($this->model, 'searchable')) {
            $this->fieldSearchable = $this->model->searchable;
        }
        return $this;
    }

    /**
     * Set Model class name
     *
     * @return string
     */
    public function setSearchable($fields = [])
    {
        $this->fieldSearchable = $fields;
        return $this;
    }

    /**
     * Set Model class name
     *
     * @return string
     */
    public function setFilterable($fields = [])
    {
        $this->fieldFilterable = $fields;
        return $this;
    }

    /**
     * Get Filterable Fields
     *
     * @return array
     */
    public function getFieldsFilterable()
    {
        return $this->fieldFilterable;
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());
        if (
            !$model instanceof Model &&
            !$model instanceof OfflineModel &&
            !$model instanceof User &&
            !$model instanceof Role
        ) {
            throw new RepositoryException(
                "Class {$this->model()} must be an instance of
                    SunAppModules\\Core\\Entities\\Model,
                    SunAppModules\\Core\\Entities\\OfflineModel
                    or SunAppModules\\Core\\Entities\\User"
            );
        }
        return $this->model = $model;
    }

    public function paginate($limit = null, $columns = ['*'], $method = 'paginate')
    {
        if ($limit == null) {
            $limit = 30;
        }
        $request = app('request');
        if ($request->input('parent_id', null)) {
            $limit = -1;
        }
        $limit = $request->input('per_page', $limit);
        if ($limit >= 0) {
            if ($this->model instanceof NestedModel) {
                if ($request->input('parent_id', null)) {
                    return parent::get($columns);
                }
                return parent::paginate($limit, $columns, $method);
            }
            return parent::paginate($limit, $columns, $method);
        }
        return parent::get($columns);
    }
}
