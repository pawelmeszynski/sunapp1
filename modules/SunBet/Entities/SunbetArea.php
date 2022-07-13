<?php

namespace SunAppModules\SunBet\Entities;

use SunAppModules\Core\Entities\Model;
use SunAppModules\SunBet\Entities\SunbetCompetition;

class SunbetArea extends Model
{
    protected $namespace = 'sunbet::areas';
    protected $fillable = ['id', 'name', 'countryCode', 'flag', 'parentAreaId', 'parentArea'];

    public function competition(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SunbetCompetition::class, 'area_id', 'id');
    }
}
