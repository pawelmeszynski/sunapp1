<?php

namespace SunAppModules\SunBet\Entities;

use SunAppModules\SunBet\Entities\SunbetStanding;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SunAppModules\Core\Entities\Model;


class SunbetTeam extends Model
{
    protected $namespace = 'sunbet::competitions';
    protected $fillable = [
        'id',
        'name',
        'shortName',
        'tla',
        'crest',
        'address',
        'website',
        'founded',
        'clubColors',
        'venue',
        'away_team_id',
        'home_team_id',
    ];

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(SunbetCompetition::class,'sunbet_competition_team',
            'team_id',
            'competition_id');
    }

    public function standings(): BelongsToMany
    {
        return $this->belongsToMany(SunbetStanding::class,
            'sunbet_standing_team',
            'team_id',
            'standing_id');
    }
}
