<?php

namespace SunAppModules\Core\Entities;

use Module;
use Silber\Bouncer\Database\Role as BaseRole;

class Role extends BaseRole
{
    protected $namespace = 'core::roles';
    protected $actions = ['show', 'edit', 'update' => 'edit', 'destroy'];
    protected $appends = ['abilities', 'required_abilities'];

    public function getActionsAttribute()
    {
        $actions = [];
        if ($this->namespace) {
            foreach ($this->actions as $action => $permission) {
                if (!is_string($action)) {
                    $action = $permission;
                }
                $routeNamespace = str_replace('::', '.', $this->namespace);
                if (\Bouncer::can($permission, $this)) {
                    $actions[$action] = route("SunApp::{$routeNamespace}.{$action}", $this);
                }
            }
        }
        return $actions;
    }

    public function getMetaParams()
    {
        $all = self::count();
        return [
            'counter' => [
                'all' => $all
            ]
        ];
    }

    public function getNamespaceAttribute()
    {
        return $this->namespace;
    }

    public function getAbilitiesAttribute()
    {
        $abilities = [];
        foreach ($this->getAbilities() as $ability) {
            $abilities[$ability->entity_type][$ability->name] = 1;
        }
        return $abilities;
    }

    public function getRequiredAbilitiesAttribute()
    {
        $required_abilities = [];
        foreach (Module::all() as $name => $module) {
            $module_json = $module->json();
            if ($module_json->has('abilities_reqiures')) {
                $abilities_requries = $module_json->get('abilities_reqiures');
                $required_abilities = array_merge($required_abilities, $abilities_requries);
            }
        }
        return $required_abilities;
    }
}
