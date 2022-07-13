<?php

namespace SunAppModules\SunBet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use SunAppModules\Core\Entities\Model;
use SunAppModules\SunBet\Entities\SunbetTeam;

class SunbetPlayer extends Model
{

    protected $namespace = 'sunbet::players';
    protected $fillable = [
        'id', 'name', 'position', 'dateOfBirth', 'nationality', 'team_id',
    ];
    protected $casts = [
        'dateOfBirth' => 'date',
    ];

    public function teams()
    {
        return $this->hasOne(SunbetTeam::class, 'id', 'team_id');
    }
}
