<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class SecurityExceptionsForm extends Form
{
    public function buildForm()
    {
        $this->add('status_code', 'text', [
            'label' => trans('core::security.status_code'),
        ]);
        $this->add('exception_type', 'text', [
            'label' => trans('core::security.exception_type'),
        ]);
        $this->add('ip_address', 'text', [
            'label' => trans('core::security.ip_address'),
            'rules' => 'required',
        ]);
        $this->add('url', 'text', [
            'label' => trans('core::security.url'),
        ]);
        $this->add('message', 'textarea', [
            'label' => trans('core::security.message'),
        ]);
        $this->add('method', 'text', [
            'label' => trans('core::security.method'),
        ]);
        $this->add('user_agent', 'textarea', [
            'label' => trans('core::security.user_agent'),
        ]);
        $this->add('created_at', 'text', [
            'label' => trans('core::security.created_at'),
        ]);
    }
}
