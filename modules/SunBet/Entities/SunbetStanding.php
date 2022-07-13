<?php

namespace SunAppModules\SunBet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SunAppModules\Core\Entities\Model;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Entities\SunbetTeam;

/**
 * App\Models\SunbetStanding
 *
 * @property int $id
 * @property string $stage
 * @property string $type
 * @property string $group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $competitions
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding query()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetStanding whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SunbetStanding extends Model
{
    protected $namespace = 'sunbet::standings';
    protected $fillable = [
        'stage', 'group', 'type','competition_id',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(SunbetTeam::class, 'sunbet_standing_team',
            'standing_id',
            'team_id')->withPivot('position', 'played_Games','form','won','draw','lost','points',
            'goals_For','goals_For','goal_Difference');
    }
    public function competition()
    {
        return $this->belongsTo(SunbetCompetition::class, 'competition_id', 'id');
    }
}
