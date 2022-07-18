<?php

namespace SunAppModules\Core\Criteria;

use Config;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria as BaseRequestCriteria;
use SunAppModules\Core\Entities\NestedModel;

/**
 * Class RequestCriteria
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class RequestCriteria extends BaseRequestCriteria implements CriteriaInterface
{
    /**
     * Escape special char for query with like condition
     * @var string[]
     */
    protected $escapedChar = [
        '%' . '\\\\',
        '\0',
        '\\n',
        '\\r',
        "\\'",
        '%' . '\\"',
        '\\Z',
    ];

    /**
     * Apply criteria in query repository
     *
     * @param  Builder|Model  $model
     * @param  RepositoryInterface  $repository
     *
     * @return mixed
     * @throws Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $repository->getFieldsSearchable();
        $fieldsFilterable = $repository->getFieldsFilterable();
        if (method_exists($model, 'translatable')) {
            $fieldsSearchableTranslatable = array_fill_keys($model->translatable() ?? [], 'like');
        } elseif (isset($model->translatable)) {
            $fieldsSearchableTranslatable = array_fill_keys($model->translatable ?? [], 'like');
        } else {
            $fieldsSearchableTranslatable = [];
        }

        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $search = str_replace('\:', '|', $search);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);

        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';

        $search = $this->escapeSpecialChar($search, $this->escapedChar);

        $lang = Config::get('app.locale', 'en');
        if (session()->has('content_lang')) {
            $lang = session()->get('content_lang')->code;
        }
        if (request()->has('content_lang')) {
            $lang = request()->get('content_lang');
        }

        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {
            $searchFields = is_array($searchFields) || is_null($searchFields)
                ? $searchFields : explode(';', $searchFields);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $fieldsTranslated = $this->parserFieldsSearch($fieldsSearchableTranslatable, $searchFields);
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $search = $this->parserSearchValue($search);
            $search = str_replace('|', ':', $search);
            $modelForceAndWhere = strtolower($searchJoin) === 'or';
            [$fields, $fieldsTranslated] = $this->prepareTranslatedFields($fields, $fieldsTranslated);

            $model = $model->where(function ($query) use (
                $model,
                $fields,
                $fieldsTranslated,
                $fieldsFilterable,
                $search,
                $searchData,
                $isFirstField,
                $modelForceAndWhere,
                $lang
            ) {
                /** @var Builder $query */
                $allSearchableFields = $fields + $fieldsTranslated;
                $allFilterableFields = $allSearchableFields + $fieldsFilterable;
                $modelTableName = $query->getModel()->getTable();
                $query->where(function ($query) use (
                    $model,
                    $allFilterableFields,
                    $fields,
                    $fieldsTranslated,
                    $search,
                    $searchData,
                    $isFirstField,
                    $modelForceAndWhere,
                    $modelTableName,
                    $lang
                ) {
                    foreach ($searchData as $key => $value) {
                        $field = $key;
                        if (isset($allFilterableFields[$key])) {
                            $condition = $allFilterableFields[$key];
                        } elseif (array_search($key, $allFilterableFields) !== false) {
                            $condition = '=';
                            $field = $key;
                        } else {
                            continue;
                        }
                        $translated = isset($fieldsTranslated[$field]);
                        $value = strtolower(($condition == 'like' || $condition == 'ilike') ? "%{$value}%" : $value);
                        $relation = null;
                        if (stripos($field, '.')) {
                            $explode = explode('.', $field);
                            $field = array_pop($explode);
                            $relation = implode('.', $explode);
                        }

//                        $relation = null;
//                        if (stripos($field, '.')) {
//                            $explode = explode('.', $field);
//                            $field = array_pop($explode);
//                            $relation = implode('.', $explode);
//                        }
                        $method = 'jsonSearch' . studly_case($field);
//                    if ($isFirstField || !$modelForceAndWhere) {
//                        if (!is_null($value)) {
//                            if (method_exists($model, $method)) {
//                                $model->{$method}($query, $field, $condition, $value);
//                            } else {
//                                if (!is_null($relation)) {
//                                    $query->whereHas($relation, function ($query) use ($field, $condition, $value) {
//                                        $query->where($field, $condition, $value);
//                                    });
//                                } else {
//                                    if ($translated) {
//                                        $field = "{$modelTableName}.{$field}->\"$.{$lang}\"";
//                                        $query->whereRaw("LOWER({$field}) {$condition} ?", [$value]);
//                                    } else {
//                                        $query->where($modelTableName.'.'.$field, $condition, $value);
//                                    }
//                                }
//                            }
//                            $isFirstField = false;
//                        }
//                    } else {
                        if (!is_null($value)) {
                            if (method_exists($model, $method)) {
                                $model->{$method}($query, $field, $condition, $value);
                            } else {
                                if (!is_null($relation)) {
                                    $query->whereHas(
                                        $relation,
                                        function ($query) use ($field, $condition, $value) {
                                            $query->where($field, $condition, $value);
                                        }
                                    );
                                } else {
                                    if ($translated) {
                                        $field = "{$modelTableName}.{$field}->\"$.{$lang}\"";
                                        $query->whereRaw("LOWER({$field}) {$condition} ?", [$value]);
                                    } else {
                                        $query->where($modelTableName . '.' . $field, $condition, $value);
                                    }
                                }
                            }
                        }
//                    }
                    }
                });
                $isFirstField = true;
                if ($search !== null) {
                    $query->where(function ($query) use (
                        $model,
                        $allSearchableFields,
                        $fields,
                        $fieldsTranslated,
                        $search,
                        $searchData,
                        $isFirstField,
                        $modelForceAndWhere,
                        $modelTableName,
                        $lang
                    ) {
                        foreach ($allSearchableFields as $field => $condition) {
                            if (is_numeric($field)) {
                                $field = $condition;
                                $condition = 'like';
                            }
                            $translated = isset($fieldsTranslated[$field]);
                            $value = null;

                            $condition = trim(strtolower($condition));

                            if (!is_null($search)) {
                                $value = strtolower(($condition == 'like' || $condition == 'ilike')
                                    ? "%{$search}%" : $search);
                            }
                            $relation = null;
                            if (stripos($field, '.')) {
                                $explode = explode('.', $field);
                                $field = array_pop($explode);
                                $relation = implode('.', $explode);
                            }
                            $method = 'jsonSearch' . studly_case($field);
                            if ($isFirstField) {
                                if (!is_null($value)) {
                                    if (method_exists($model, $method)) {
                                        $query->where(function ($query) use (
                                            $model,
                                            $method,
                                            $field,
                                            $condition,
                                            $value
                                        ) {
                                            $model->{$method}($query, $field, $condition, $value);
                                        });
                                    } else {
                                        if (!is_null($relation)) {
                                            $query->whereHas(
                                                $relation,
                                                function ($query) use ($field, $condition, $value) {
                                                    $query->where($field, $condition, $value);
                                                }
                                            );
                                        } else {
                                            if ($translated) {
                                                $field = "{$modelTableName}.{$field}->\"$.{$lang}\"";
                                                $query->whereRaw("LOWER({$field}) {$condition} ?", [$value]);
                                            } else {
                                                $query->where($modelTableName . '.' . $field, $condition, $value);
                                            }
                                        }
                                    }
                                    $isFirstField = false;
                                }
                            } else {
                                if (!is_null($value)) {
                                    if (method_exists($model, $method)) {
                                        $query->orWhere(function ($query) use (
                                            $model,
                                            $method,
                                            $field,
                                            $condition,
                                            $value
                                        ) {
                                            $model->{$method}($query, $field, $condition, $value);
                                        });
                                    } else {
                                        if (!is_null($relation)) {
                                            $query->orWhereHas(
                                                $relation,
                                                function ($query) use ($field, $condition, $value) {
                                                    $query->where($field, $condition, $value);
                                                }
                                            );
                                        } else {
                                            if ($translated) {
                                                $field = "{$modelTableName}.{$field}->\"$.{$lang}\"";
                                                $query->orWhereRaw("LOWER({$field}) {$condition} ?", [$value]);
                                            } else {
                                                $query->orWhere($modelTableName . '.' . $field, $condition, $value);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
        if ($model instanceof NestedModel) {
            $parent_id = $this->request->get('parent_id', null);
            $levels = $this->request->get('levels', null);

            $min_depth = 0;
            if (!$this->request->has('from_depth') && !$parent_id) {
                $min_depth = $model->min('depth');
            }
            $from_depth = $this->request->get('from_depth', $min_depth);

            if ($parent_id) {
                $parent = clone $model;
                $parent = $parent->findOrFail($parent_id);

                $model = $model->where(function ($q) use ($parent) {
                    $q->where('_lft', '>', $parent->_lft);
                    $q->where('_rgt', '<', $parent->_rgt);
                });

                if ($levels) {
                    $model = $model->where(function ($q) use ($parent, $levels, $from_depth) {
                        $q->where('depth', '>=', (int) $parent->depth + (int) $from_depth);
                        $q->where('depth', '<=', (int) $parent->depth + (int) $from_depth + (int) $levels);
                    });
                } else {
                    $model = $model->where('depth', '>=', (int) $parent->depth + (int) $from_depth);
                }
            } else {
                if ($levels) {
                    $model = $model->where(function ($q) use ($levels, $from_depth) {
                        $q->where('depth', '>=', (int) $from_depth);
                        $q->where('depth', '<=', (int) $from_depth + (int) $levels - 1);
                    });
                } else {
                    $model = $model->where('depth', '>=', (int) $from_depth);
                }
            }
            $model = $model->defaultOrder();
        } elseif (isset($orderBy) && !empty($orderBy)) {
            $split = explode('|', $orderBy);
            if (count($split) > 1) {
                $table = $model->getModel()->getTable();
                $sortTable = $split[0];
                $sortColumn = $split[1];

                $split = explode(':', $sortTable);
                if (count($split) > 1) {
                    $sortTable = $split[0];
                    $keyName = $table . '.' . $split[1];
                } else {
                    $prefix = str_singular($sortTable);
                    $keyName = $table . '.' . $prefix . '_id';
                }

                $model = $model
                    ->leftJoin($sortTable, $keyName, '=', $sortTable . '.id')
                    ->orderBy($sortColumn, $sortedBy)
                    ->addSelect($table . '.*');
            } else {
                if (isset($fieldsSearchableTranslatable[$orderBy])) {
                    $model = $model->orderBy("{$orderBy}->{$lang}", $sortedBy);
                } else {
                    $model = $model->orderBy($orderBy, $sortedBy);
                }
            }
        } else {
            if ($realModel = $model->getModel()) {
                $table = $realModel->getTable();
            } else {
                $table = $model->getTable();
            }

            if (Schema::hasColumn($table, 'order')) {
                $model = $model->orderBy('order');
            } elseif (method_exists($model, 'getDefaultOrderColumn') && $model->getDefaultOrderColumn()) {
                $model = $model->defaultOrder();
            }
        }

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $model = $model->select($filter);
        }

        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }
        return $model;
    }

    protected function prepareTranslatedFields($fields, $fieldsTranslated)
    {
        $newFieldsTranslated = [];
        foreach ($fields as $key => $value) {
            if (!is_numeric($key) && isset($fieldsTranslated[$key])) {
                unset($fields[$key]);
                $newFieldsTranslated[$key] = $value;
            }
            if (is_numeric($key) && isset($fieldsTranslated[$value])) {
                unset($fields[$key]);
            }
        }
        return [$fields, $newFieldsTranslated];
    }

    /**
     * Similar to mysqli_real_escape_string without mysqli connection object
     * @param $input
     * @param $escapedChar
     * @return array|string|string[]
     */
    protected function escapeSpecialChar($input, $escapedChar)
    {
        if (is_array($input)) {
            return array_map(__METHOD__, $input);
        }

        if (!empty($input) && is_string($input)) {
            return str_replace(
                ['\\', '\0', "\n", "\r", "'", '"', "\xla",],
                $escapedChar,
                $input
            );
        }

        return $input;
    }
}
