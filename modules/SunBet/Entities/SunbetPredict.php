<?php

namespace SunAppModules\SunBet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SunAppModules\SunBet\Entities\SunbetUser;
use SunAppModules\Core\Entities\Model;
use SunAppModules\SunBet\Entities\SunbetCompetition;

/**
 * App\Models\SunbetPredict
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $match_id
 * @property int|null $home_team_goals
 * @property int|null $away_team_goals
 * @property int|null $competition_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition[] $competition
 * @property-read int|null $competition_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SunbetSchedule[] $schedule
 * @property-read int|null $schedule_count
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict query()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereAwayTeamGoals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereCompetitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereHomeTeamGoals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereMatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetPredict whereUserId($value)
 * @mixin \Eloquent
 */
class SunbetPredict extends Model
{
    protected $namespace = 'sunbet::predicts';
    protected $fillable = [
        'id', 'user_id', 'match_id', 'home_team_goals', 'away_team_goals', 'competition_id',
    ];

    public function competition(): HasMany
    {
        return $this->hasMany(SunbetCompetition::class, 'competition_id', 'id');
    }
    public function schedule(): HasMany
    {
        return $this->hasMany(SunbetSchedule::class, 'match_id', 'id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(SunbetUser::class, 'id', 'user_id');
    }
}
