<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use User;

class UserGroup extends NestedModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $namespace = 'core::groups';
    protected $searchable = [
        'ext_id' => '=',
        'name' => 'like',
        'description' => 'like',
        'core' => '=',
    ];
    protected $fillable = [
        'ext_id',
        'name',
        'description',
        'core',
    ];
    protected $appends = ['nested_name', 'children_count'];

    public function __construct(array $attributes = [])
    {
        if (!config('system.front')) {
            $this->appends = array_merge($this->appends, $this->defaults_appends);
        }
        parent::__construct($attributes);
    }

    /**
     * The users that belong to the group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group');
    }

    public static function groupCount()
    {
        $all = UserGroup::count();
        $trashed = UserGroup::onlyTrashed()->count();
        return [
            'all' => $all,
            'trashed' => $trashed
        ];
    }

    public function getMetaParams()
    {
        $all = UserGroup::count();
        $trashed = UserGroup::onlyTrashed()->count();
        return [
            'counter' => [
                'all' => $all,
                'trashed' => $trashed
            ]
        ];
    }
}
