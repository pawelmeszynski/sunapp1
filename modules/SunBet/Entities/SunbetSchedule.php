<?php

namespace SunAppModules\SunBet\Entities;

use SunAppModules\Core\Entities\Model;

/**
 * App\Models\SunbetSchedule
 *
 * @property int $id
 * @property int|null $home_team_id
 * @property int|null $away_team_id
 * @property string $utc_date
 * @property string|null $status
 * @property int|null $matchday
 * @property string|null $stage
 * @property string|null $group
 * @property string|null $last_updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $home
 * @property int $away
 * @property int $points_calculated
 * @property-read \App\Models\Team|null $awayTeam
 * @property-read \App\Models\Team|null $homeTeam
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SunbetPredict[] $predicts
 * @property-read int|null $predicts_count
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereAway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereAwayTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereHome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereHomeTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereLastUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereMatchday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule wherePointsCalculated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetSchedule whereUtcDate($value)
 * @mixin \Eloquent
 */
class SunbetSchedule extends Model
{
    protected $namespace = 'sunbet::schedules';
    protected $fillable = [
        'id',
        'competition_id',
        'utc_date',
        'status',
        'matchday',
        'stage',
        'group',
        'last_updated_at',
        'away_team_id',
        'home_team_id',
        'home',
        'away',
        'points_calculated',
    ];

    protected $casts = [
        'utc_date' => 'date',
        'last_updated_at' => 'date',
        'points_calculated' => 'boolean',
    ];

    public function awayTeam(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SunbetTeam::class, 'id', 'away_team_id');
    }

    public function homeTeam(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SunbetTeam::class, 'id', 'home_team_id');
    }

    public function predicts()
    {
        return $this->hasMany(SunbetPredict::class, 'match_id', 'id');
    }

    public function competition()
    {
        return $this->hasMany(SunbetCompetition::class, 'competition_id', 'id');
    }
}
