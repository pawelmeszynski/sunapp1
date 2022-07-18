<?php

namespace SunAppModules\Core\Entities;

use Illuminate\Database\Eloquent\Model as DefaultModel;

class LoginAsUser extends DefaultModel
{
    protected $fillable = [
        'user_entity_from',
        'user_id_from',
        'user_entity_to',
        'user_id_to',
        'token'
    ];
    protected $dates = ['created_at'];
    public const UPDATED_AT = null;
}
