<?php

namespace SunAppModules\SunBet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SunAppModules\Core\Entities\Model;
use SunAppModules\SunBet\Entities\SunbetTeam;

class SunbetCompetition extends Model
{
    protected $namespace = 'sunbet::competitions';
    protected $fillable = [
        'id',
        'ext_id',
        'name',
        'code',
        'type',
        'emblem',
        'plan',
        'area_id',
        'status',
        'sync'
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(SunbetTeam::class);
    }

    public function area()
    {
        return $this->hasOne(SunbetArea::class, 'id', 'area_id');
    }

    public function predict()
    {
        return $this->hasOne(SunbetPredict::class,
            'id',
            'competition_id');
    }

    public function schedule()
    {
        return $this->hasOne(SunbetSchedule::class,
            'id',
            'competition_id');
    }

    public function standings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SunbetStanding::class);
    }
}
