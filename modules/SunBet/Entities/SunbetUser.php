<?php

namespace SunAppModules\SunBet\Entities;

use Illuminate\Notifications\DatabaseNotification;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SunAppModules\Core\Entities\User;

/**
 * App\Models\SunbetUser
 *
 * @property int $id
 * @property int $points
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SunbetUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SunbetUser extends User
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $namespace = 'sunbet::users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];
}
