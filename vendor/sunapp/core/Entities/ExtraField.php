<?php

namespace SunAppModules\Core\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

class ExtraField extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'cast', 'translatable', 'options'];
    protected $namespace = 'core::extra-fields';
    protected $attributes = [
        'options' => '{}',
    ];
    protected $appends = ['entities'];

    public function entities()
    {
        return $this->hasMany(ExtraFieldEntity::class, 'extra_field_id');
    }

    public function getEntitiesAttribute()
    {
        return $this->entities()->pluck('entity_type');
    }

    public function getMetaParams()
    {
        return [
            'counter' => [
                'all' => self::count(),
                'trashed' => self::onlyTrashed()->count()
            ]
        ];
    }
}
