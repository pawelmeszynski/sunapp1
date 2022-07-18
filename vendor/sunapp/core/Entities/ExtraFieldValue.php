<?php

namespace SunAppModules\Core\Entities;

use Cache;

class ExtraFieldValue extends Model
{
    protected $fillable = ['*'];

    public function getTableColumns()
    {
        $that = $this;
        return Cache::remember('extraFieldValueGetTableColumns', 10, function () use ($that) {
            return collect(array_flip(
                $that->getConnection()->getSchemaBuilder()
                    ->getColumnListing($that->getTable())
            ))->except(
                'id',
                'entity_type',
                'entity_id',
                'created_at',
                'updated_at',
                'deleted_at',
                'createdBy',
                'updatedBy',
                'deletedBy'
            )->flip()->values()->all();
        });
    }
}
