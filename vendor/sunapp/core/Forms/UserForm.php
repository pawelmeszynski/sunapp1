<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\Entities\Role;
use SunAppModules\Core\Entities\UserGroup;
use SunAppModules\Core\src\FormBuilder\Form;

class UserForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => trans('core::users.name'),
            'rules' => 'required',
        ]);
        if ($this->getModel()->is_ldap) {
            $this->add('ldap_name', 'text', [
                'label' => trans('core::users.ldap_name'),
                'attr' => ['readonly' => true, 'disabled' => true]
            ]);
        } else {
            $this->add('ldap_name', 'text', [
                'label' => trans('core::users.ldap_name'),
                'attr' => ['readonly' => true, 'disabled' => true]
            ]);
            /*$this->remove('ldap_name');*/
        }
        $this->add('email', 'email', [
            'label' => trans('core::users.email'),
            'rules' => "required|email|unique:users,email,{$this->getModel()->email},email",
        ]);
        $this->add('password', 'password', [
            'label' => trans('core::users.password'),
            'rules' => 'same:password_confirmation'
        ]);
        $this->add('password_confirmation', 'password', [
            'label' => trans('core::users.password_confirmation'),
            'rules' => 'required|same:password',
            'vue-model-off' => true
        ]);
        if ($this->getModel()->password) {
            $this->modify('password', 'password', [
                'label' => trans('core::users.password'),
                'attr' => ['placeholder' => trans('core::users.no_changes')],
                'rules' => ''
            ]);
            $this->modify('password_confirmation', 'password', [
                'label' => trans('core::users.password_confirmation'),
                'vue-model-off' => true,
                'rules' => ''
            ]);
        }
        if ($this->getModel()->is_ldap) {
            $this->modify('name', 'text', [
                'attr' => ['disabled' => true, 'readonly' => true],
                'label' => trans('core::users.name'),
                'rules' => '',
            ], true);
            $this->modify('email', 'email', [
                'label' => trans('core::users.email'),
                'attr' => ['disabled' => true, 'readonly' => true],
            ], true);
            $this->modify('password', 'password', [
                'label' => trans('core::users.password'),
                'rules' => 'same:password_confirmation',
                'attr' => ['readonly' => true]
            ], true);
            $this->modify('password_confirmation', 'password', [
                'label' => trans('core::users.password_confirmation'),
                'vue-model-off' => true,
                'attr' => ['readonly' => true]
            ], true);
            /*$this->remove('password');
            $this->remove('password_confirmation');*/
        }
        $this->add('api_token', 'text', [
            'label' => trans('core::users.api_token'),
            'rules' => 'nullable|between:64,250',
        ]);
        // if ($this->getModel()->email_verified_at == null) {
        $this->add('email_verify', 'checkbox', [
            'label' => trans('core::users.activate_account'),
            'value' => 1,
            'checked' => true
        ]);
        // }

        $groups = UserGroup::all();
        $groups_data = [];
        foreach ($groups as $group) {
            $groups_data[$group->id] = $group->nested_name . ' (#' . $group->id . ')';
        }
        $this->add('user_group', 'select', [
            'label' => trans('core::users.groups'),
            'attr' => ['data-url' => route('SunApp::core.groups.index'), 'multiple' => 'multiple'],
            'choices' => $groups_data,
            'rules' => 'required',
        ]);

        $roles = Role::all();
        $roles_data = [];
        foreach ($roles as $role) {
            $roles_data[$role->id] = $role->name . ' (#' . $role->id . ')';
        }
        $this->add('user_role', 'select', [
            'label' => trans('core::users.roles'),
            'attr' => ['data-url' => route('SunApp::core.roles.index'), 'multiple' => 'multiple'],
            'choices' => $roles_data,
        ]);

        if ($this->hasType('server_files')) {
            $this->add('images', 'server_files', [
                'label' => trans('core::actions.images'),
                'multiple' => true,
            ]);
        }
    }
}
