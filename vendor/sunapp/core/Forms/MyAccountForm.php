<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class MyAccountForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => trans('core::users.name'),
            'rules' => 'required',
        ]);

        $this->add('password', 'password', [
            'label' => trans('core::users.password'),
            'rules' => 'same:password_confirmation'
        ]);

        $this->add('password_confirmation', 'password', [
            'label' => trans('core::users.password_confirmation'),
            'rules' => 'required_with:password|same:password',
            'vue-model-off' => true
        ]);

        if ($this->getModel()->password) {
            $this->modify('password', 'password', [
                'label' => trans('core::users.password'),
                'attr' => ['placeholder' => trans('core::users.no_changes')]
            ]);
        }

        $this->add('api_token', 'text', [
            'label' => trans('core::users.api_token'),
            'rules' => 'nullable|between:64,250',
        ]);

        if ($this->hasType('server_files')) {
            $this->add('images', 'server_files', [
                'label' => trans('core::actions.images'),
                'multiple' => true,
            ]);
        }
    }
}
