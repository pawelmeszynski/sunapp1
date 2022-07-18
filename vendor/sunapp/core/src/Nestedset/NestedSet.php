<?php

namespace SunAppModules\Core\src\Nestedset;

use Illuminate\Database\Schema\Blueprint;
use Kalnoy\Nestedset\NestedSet as BaseNestedSet;

class NestedSet extends BaseNestedSet
{
    /**
     * The name of default parent id column.
     */
    public const DEPTH = 'depth';

    /**
     * Add default nested set columns to the table. Also create an index.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public static function columns(Blueprint $table)
    {
        $table->unsignedInteger(self::LFT)->default(0);
        $table->unsignedInteger(self::RGT)->default(0);
        $table->integer(self::DEPTH)->nullable();
        $table->unsignedInteger(self::PARENT_ID)->nullable();

        $table->index(static::getDefaultColumns());
    }

    /**
     * Drop NestedSet columns.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public static function dropColumns(Blueprint $table)
    {
        $columns = static::getDefaultColumns();

        $table->dropIndex($columns);
        $table->dropColumn($columns);
    }

    /**
     * Get a list of default columns.
     *
     * @return array
     */
    public static function getDefaultColumns()
    {
        return [ static::LFT, static::RGT, static::PARENT_ID, static::DEPTH ];
    }
}
