<?php

namespace SunAppModules\Core\Traits;

use Illuminate\Support\Str;
use SunAppModules\Core\Entities\ExtraField;
use SunAppModules\Core\Entities\ExtraFieldValue;
use SunAppModules\Shop\Entities\ShopProduct;

trait ExtraFields
{
    protected static $field_prefix = 'extra';
    protected static $field_prefix_separator = '_';
    protected static $disabledExtrafieldsRoutes = [];

    protected static function bootExtraFields()
    {
        if (!config('system.front') && !app()->runningInConsole()) {
            static::retrieved(function ($model) {
                if (
                    request()->route()
                    && str_contains(
                        request()->route()->getName(),
                        str_replace('::', '.', $model->namespace)
                    )
                    && count(request()->route()->parametersWithoutNulls())
                    && !in_array(request()->route()->getName(), get_class($model)::$disabledExtrafieldsRoutes)
                ) {
                    if ($model->extraFields()->count()) {
                        foreach ($model->extraFields()->get() as $extra_field) {
                            $extra_field_name = Str::slug($extra_field->name, '_');
                            $extra_field_full_name = static::$field_prefix . static::$field_prefix_separator
                                . $extra_field_name;
                            $model->setAttribute(
                                $extra_field_full_name,
                                $model->getExtraValueByName($extra_field_name)
                            );
                        }
                    }
                }
            });
        }

        static::saving(function ($model) {
            $baseExtraFieldModel = new ExtraFieldValue();
            foreach ($baseExtraFieldModel->getTableColumns() as $extra_key) {
                unset($model->{static::$field_prefix . static::$field_prefix_separator . $extra_key});
            }
            unset($model->extra_values_loaded);
        });

        static::saved(function ($model) {
            if (!$model instanceof ExtraFieldValue) {
                $baseExtraFieldModel = new ExtraFieldValue();
                $extra_values_request = request()->only(array_map(function ($value) {
                    return static::$field_prefix . static::$field_prefix_separator . $value;
                }, $baseExtraFieldModel->getTableColumns()));
                if (count($extra_values_request)) {
                    $modelExtraFieldValues = $model->extraValuesRelation()->firstOrCreate([]);
                    $extra_values_request = collect($extra_values_request)->mapWithKeys(function ($value, $key) {
                        return [
                            str_replace(static::$field_prefix . static::$field_prefix_separator, '', $key) => $value
                        ];
                    })->all();
                    foreach ($extra_values_request as $field => $value) {
                        $modelExtraFieldValues->{$field} = $value;
                    }
                    $modelExtraFieldValues->save();
                }
            }
        });
    }

    public function extraValuesRelation()
    {
        return $this->morphOne(ExtraFieldValue::class, 'entity');
    }

    public function getFieldPrefixAttribute()
    {
        return static::$field_prefix . static::$field_prefix_separator;
    }

    public function getExtraValue($id)
    {
        if ($extra_field = $this->extraFields()->find($id)) {
            return $this->getExtraValueByName($extra_field->name);
        }

        return null;
    }

    public function extraFields()
    {
        return ExtraField::whereHas('entities', function ($q) {
            $q->where('entity_type', static::class);
            $q->where(function ($q) {
                $q->whereNull('entity_id');
                $q->orWhere('entity_id', $this->id);
            });
        });
    }

    public function getExtraValueByName($name)
    {
        $temp_name = $this->field_prefix . Str::slug($name, '_');
        if ($this->{$temp_name} !== null) {
            return $this->{$temp_name};
        }
        if ($this->extra_values_loaded !== true) {
            $this->extra_values;
            if ($value = $this->{$temp_name}) {
                return $value;
            }
        }
        return null;
    }

    public function getExtraValueName($id, $prefix = true)
    {
        $name = null;
        if ($extra_field = $this->extraFields()->find($id)) {
            $name = $extra_field->name;
            if ($prefix) {
                $name = static::$field_prefix . static::$field_prefix_separator . $name;
            }
        }

        return $name;
    }

    public function getExtraValuesAttribute()
    {
        $extra_values = $this->extraValuesRelation;
        if ($extra_values) {
            foreach ($this->extraFields()->get() as $extra_field) {
                $extra_field_name = Str::slug($extra_field->name, '_');
                $extra_field_full_name = static::$field_prefix . static::$field_prefix_separator
                    . $extra_field_name;
                $this->setAttribute(
                    $extra_field_full_name,
                    $extra_values->{Str::slug($extra_field->name, '_')}
                );
                if ($extra_field->translatable) {
                    $this->translatable = array_merge(
                        $this->translatable ?? [],
                        [$extra_field_full_name]
                    );
                } else {
                    if ($extra_field->cast) {
                        $this->casts = array_merge(
                            $this->casts ?? [],
                            [$extra_field_full_name => $extra_field->cast]
                        );
                    }
                }
                if ($extra_field->type == 'editor') {
                    $this->blade_process = array_merge(
                        $this->blade_process ?? [],
                        [$extra_field_full_name]
                    );
                }
            }
        }
        $this->extra_values_loaded = true;
        return $this;
    }
}
