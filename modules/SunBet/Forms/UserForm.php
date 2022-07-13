<?php

namespace SunAppModules\Sunbet\Forms;

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
        $this->add('email', 'email', [
            'label' => trans('core::users.email'),
            'rules' => "required|email|unique:users,email,{$this->getModel()->email},email",
        ]);
        $this->add('password', 'password', [
            'label' => trans('core::users.password'),
            'rules' => 'same:password_confirmation'
        ]);
        $this->add('points', 'number', [
            'label' => 'points',
            'attr' =>['readonly' => false, 'disabled' => true]
        ]);
        $this->add('password_confirmation', 'password', [
            'label' => trans('core::users.password_confirmation'),
            'rules' => 'required|same:password',
            'vue-model-off' => true
        ]);
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
            $this->modify('points', 'number', [
                'label' => 'points',
                'attr' => ['disabled' => false, 'readonly' => false]
            ]);
            $this->modify('password', 'password', [
                'label' => trans('core::users.password'),
                'rules' => 'same:password_confirmation',
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
    }
}
