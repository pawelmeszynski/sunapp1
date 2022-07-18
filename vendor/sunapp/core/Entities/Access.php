<?php

namespace SunAppModules\Core\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

class Access extends Model
{
    use SoftDeletes;

    protected $namespace = 'core::access';
    protected $searchable = [
        'ip_address_mask',
        'w_2fa',
    ];
    protected $fillable = [
        'ip_address_mask',
        'w_2fa',
    ];

    public function getMetaParams()
    {
        $all = Access::count();
        $trashed = Access::onlyTrashed()->count();
        return [
            'counter' => [
                'all' => $all,
                'trashed' => $trashed,
            ]
        ];
    }
}
