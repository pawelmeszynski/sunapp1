<?php

namespace SunAppModules\SunBet\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class UserForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => 'Name',
            'rules' => 'required',
        ]);
        $this->add('email', 'email', [
            'label' => 'Email',
            'rules' => "required|email|unique:sunbet_users,email,{$this->getModel()->email},email",
        ]);
        $this->add('password', 'password', [
            'label' => 'Password',
            'rules' => 'same:password_confirmation'
        ]);
        $this->add('points', 'number', [
            'label' => 'points',
            'attr' => ['readonly' => false, 'disabled' => true]
        ]);
        $this->add('password_confirmation', 'password', [
            'label' => 'password_confirmation',
            'rules' => 'required|same:password',
            'vue-model-off' => true
        ]);
        if ($this->getModel()->is_ldap) {
            $this->modify('name', 'text', [
                'attr' => ['disabled' => true, 'readonly' => true],
                'label' => 'Name',
                'rules' => '',
            ], true);
            $this->modify('email', 'email', [
                'label' => 'Email',
                'attr' => ['disabled' => true, 'readonly' => true],
            ], true);
            $this->modify('points', 'number', [
                'label' => 'points',
                'attr' => ['disabled' => false, 'readonly' => false]
            ]);
            $this->modify('password', 'password', [
                'label' => 'Password',
                'rules' => 'same:password_confirmation',
                'attr' => ['readonly' => true]
            ], true);
            /*$this->remove('password');
            $this->remove('password_confirmation');*/
        }
    }
}
