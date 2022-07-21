<?php

namespace SunAppModules\Core\Entities;

use Bouncer;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use SunAppModules\Core\Http\Notifications\ResetPassword;
use SunAppModules\Core\Http\Notifications\VerifyEmail;
use SunAppModules\Core\Traits\ExtraFields;
use SunAppModules\Core\Traits\MacroableModel;

class User extends Authenticatable implements MustVerifyEmail, AuditableContract
{
    use SoftDeletes;
    use Notifiable;
    use HasRolesAndAbilities;
    use Auditable;
    use MacroableModel;
    use ExtraFields;

    protected $actions = [
        'show',
        'edit',
        'update' => 'edit',
        'destroy',
        'ban' => 'edit',
        'super' => 'edit',
        'login_as' => 'show',
        'enable2fa',
        'reset2fa'
    ];
    protected $trashedActions = ['show' => '?trashed=only', 'update' => '?restore=true', 'destroy' => '?force=true'];
    protected $namespace = 'core::users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google2fa_secret',
        'is2fa_google_enabled',
        'verified_at_2fa_google',
        'api_token',
        'is_ldap',
        'ldap_name'
    ];

    protected $appends = ['superadmin', 'banned', 'user_group', 'user_role', 'login_as_url'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'logged_at' => 'datetime'
    ];

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        if (env('CMS_USER_CREATE_NOTIFY', false)) {
            $this->notify(new VerifyEmail());
        }
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * The groups that belong to the user.
     */
    public function groups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_group');
    }

    public function getUserGroupAttribute()
    {
        $groups = $this->groups()->pluck('user_group_id')->toArray();
        if ($groups && isset($groups[0])) {
            return $groups;
        }
        return [];
    }

    public function getUserRoleAttribute()
    {
        $groups = $this->roles()->pluck('role_id')->toArray();
        if ($groups && isset($groups[0])) {
            return $groups;
        }
        return [];
    }

    public function inGroup($id)
    {
        if (!is_numeric($id) && !$id instanceof Group) {
            $type = gettype($id);
            throw new Exception("Element must be an instance of
            SunAppModules\\Core\\Entities\\Group or be an integer, the {$type} is given");
        }
        if ($id instanceof Group) {
            $id = $id->id;
        }
        return $this->groups()->find($id) ? true : false;
    }

    public function inGroupTree(...$groups)
    {
        if (
            count($groups) === 1 && (is_array($groups[0])
                || $groups[0] instanceof Collection)
        ) {
            $groups = $groups[0];
        }
        if (count($groups) == 0) {
            return true;
        }
        foreach ($groups as $group) {
            if (!is_numeric($group) && !$group instanceof UserGroup) {
                $type = gettype($group);
                throw new Exception("Element must be an instance of
                SunAppModules\\Core\\Entities\\Group or be an integer, the {$type} is given");
            }
            if (is_numeric($group)) {
                $group = Group::find($group);
            }
            if ($this->groups->where('_lft', '>=', $group->_lft)->where('_rgt', '<=', $group->_rgt)->count()) {
                return true;
            }
        }
        return false;
    }

    public function getActionsAttribute()
    {
        $actions = [];
        if ($this->namespace) {
            if (!$this->trashed()) {
                foreach ($this->actions as $action => $permission) {
                    if (!is_string($action)) {
                        $action = $permission;
                    }
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (Bouncer::can($permission, $this)) {
                        $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this);
                    }
                }
            } else {
                foreach ($this->trashedActions as $action => $params) {
                    $routeNamespace = str_replace('::', '.', $this->namespace);
                    if (Bouncer::can($action, $this)) {
                        $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this) . $params;
                    }
                }
            }
        }
        return $actions;
    }

    public static function groupCount()
    {
        $all = User::count();
        $active = User::whereNotNull('email_verified_at')->count();
        // $inactive = User::whereNull('email_verified_at')->count();
        $inactive = (int)($all - $active);
        $banned = User::whereHas('abilities', function ($q) {
            $q->where('permissions.forbidden', 1);
            $q->where('abilities.name', '*')->where('abilities.entity_type', '*');
        })->count();
        $trashed = User::onlyTrashed()->count();
        return [
            'all' => $all,
            'active' => $active,
            'inactive' => $inactive,
            'banned' => $banned,
            'trashed' => $trashed
        ];
    }

    public function getMetaParams()
    {
        $all = User::count();
        $verified = User::whereNotNull('email_verified_at')->count();
        // $inactive = User::whereNull('email_verified_at')->count();
        $not_verified = (int)($all - $verified);
        $banned = User::whereHas('abilities', function ($q) {
            $q->where('permissions.forbidden', 1);
            $q->where('abilities.name', '*')->where('abilities.entity_type', '*');
        })->count();
        $superadmins = User::whereHas('abilities', function ($q) {
            $q->where('permissions.forbidden', 0);
            $q->where('abilities.name', '*')->where('abilities.entity_type', '*');
        })->count();
        $ldap = User::where('is_ldap', 1)->count();
        $not_ldap = $all - $ldap;
        $trashed = User::onlyTrashed()->count();
        return [
            'counter' => [
                'all' => $all,
                'verified' => $verified,
                'not_verified' => $not_verified,
                'banned' => $banned,
                'superadmins' => $superadmins,
                'ldap' => $ldap,
                'not_ldap' => $not_ldap,
                'trashed' => $trashed
            ]
        ];
    }

    public function getNamespaceAttribute()
    {
        return $this->namespace;
    }

    public function getSuperadminAttribute()
    {
        return $this->can(1);
    }

    public function getBannedAttribute()
    {
        $forbiddenAbilities = $this->getForbiddenAbilities()->pluck('id')->toArray();
        if (in_array(1, $forbiddenAbilities)) {
            return true;
        }
        return false;
    }

    public function jsonSearchEmailVerifiedAt($query, $field, $condition, $value)
    {
        if ($value == '1') {
            $query->whereNotNull($field);
        } else {
            $query->whereNull($field);
        }
    }

    public function jsonSearchBanned($query, $field, $condition, $value)
    {
        if ($value == '1') {
            $query->whereHas('abilities', function ($q) {
                $q->where('permissions.forbidden', 1);
                $q->where('abilities.name', '*')->where('abilities.entity_type', '*');
            });
        }
    }

    public function jsonSearchSuperadmin($query, $field, $condition, $value)
    {
        if ($value == '1') {
            $query->whereHas('abilities', function ($q) {
                $q->where('permissions.forbidden', 0);
                $q->where('abilities.name', '*')->where('abilities.entity_type', '*');
            });
        }
    }

    public function jsonSearchLdap($query, $field, $condition, $value)
    {
        if (intval($value)) {
            $query->where('is_ldap', $value);
        }
    }

    public function getLoginAsUrlAttribute()
    {
        return route('SunApp::core.users.login_as', [$this->id]);
    }

    public function setGoogle2faSecretAttribute($value)
    {
        $this->attributes['google2fa_secret'] = encrypt($value);
    }

    public function getVerifiedAt2faGoogle()
    {
        return (isset($this->verified_at_2fa_google)) ? $this->verified_at_2fa_google : null;
    }

    public function getIs2faGoogleEnabled()
    {
        return (isset($this->is2fa_google_enabled)) ? $this->is2fa_google_enabled : null;
    }

    public function getQrCode2faGoogle()
    {
        $google2fa = app('pragmarx.google2fa');

        //showing user acutal qr code
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name') . ' - ' . url('/'),
            $this->email,
            $this->google2fa_secret
        );
        return $QR_Image;
    }

    public function getGoogle2faSecretAttribute($value)
    {
        if (!is_null($value) && $this->getIs2faGoogleEnabled()) {
            return decrypt($value);
        }
    }
}
