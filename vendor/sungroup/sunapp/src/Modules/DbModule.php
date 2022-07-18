<?php

/**
 * Created by IntelliJ IDEA.
 * User: sunpapi
 * Date: 18.03.2019
 * Time: 12:26
 */

namespace SunApp\Modules;

use Illuminate\Database\Eloquent\Model;

class DbModule extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'name',
        'alias',
        'path',
        'keywords',
        'requires',
        'description',
        'version',
        'versions',
        'composer'
    ];

    protected $casts = [
        'keywords' => 'array',
        'requires' => 'array',
        'versions' => 'array',
    ];

    public function setVersions($version, $value)
    {
        $versions = $this->versions;
        $versions[$version] = $value;
        $this->versions = $versions;
        return $this->save();
    }
}
