<?php

namespace SunAppModules\Core\Entities;

use SunAppModules\Core\Entities\Model;
use Module;

class Audit extends Model
{
    protected $fillable = [];
    protected $appends = [
        'name',
        'old_value',
        'new_value',
        'element_name',
        'user_name'
    ];

    public function getNameAttribute()
    {
        $translation = null;
        foreach (Module::all() as $name => $module) {
            $module_json = $module->json();
            if ($module_json->get('abilities')) {
                $abilities = $module_json->get('abilities');
                if (isset($abilities['\\' . $this->auditable_type])) {
                    $translation = $abilities['\\' . $this->auditable_type];
                    $translation = trans($translation);
                    break;
                } else {
                    foreach ($abilities as $ability) {
                        if (is_array($ability) && isset($ability['\\' . $this->auditable_type])) {
                            $translation = $ability['\\' . $this->auditable_type];
                            $translation = trans($translation);
                        }
                    }
                }
            }
        }
        if (!$translation) {
            $translation = $this->auditable_type;
        }
        return $translation;
    }

    public function getElementNameAttribute()
    {
        $class = $this->auditable_type;
        $model = new $class();
        $element = $model::find($this->auditable_id);
        if (isset($element->name)) {
            $name = $element->name;
        } elseif (isset($element->number)) {
            $name = $element->number;
        } else {
            $name = $class;
        }
        return '(#' . $this->auditable_id . ') ' . $name;
    }

    public function getOldValueAttribute($value = null, $tabs = null)
    {
        if ($value) {
            $changes = json_decode($value);
            foreach ($changes as $key => $value) {
                $changes->$key = json_encode($value);
            }
        } else {
            $changes = json_decode($this->new_values);
        }
        return html_entity_decode(
            view('core::audits.changesData')
            ->with([
                'changes' => $changes,
                'audit' => get_class($this),
                'function' => 'old',
                'tabs' => $tabs + 1
            ])->render()
        );
    }

    public function getNewValueAttribute($value = null, $tabs = null)
    {
        if ($value) {
            $changes = json_decode($value);
            foreach ($changes as $key => $value) {
                $changes->$key = json_encode($value);
            }
        } else {
            $changes = json_decode($this->new_values);
        }
        return html_entity_decode(
            view('core::audits.changesData')
            ->with([
                'changes' => $changes,
                'audit' => get_class($this),
                'function' => 'new',
                'tabs' => $tabs + 1
            ])->render()
        );
    }

    public function isJson($string)
    {
        $decode = json_decode($string);
        $result = false;
        if (
            $decode &&
            gettype($decode) == 'object'
        ) {
            $result = true;
        }
        return $result;
    }

    public function getUserNameAttribute()
    {
        if ($this->user_type) {
            $user = $this->user_type;
            $user = new $user();
            $user = $user->where('id', $this->user_id)->first();
            if (isset($user->name)) {
                return '(#' . $user->id . ')' . $user->name;
            }
            return '(#' . $user->id . ')' . $user->email;
        }
        return trans('core::users.unknown');
    }
}
