<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class LocksForm extends Form
{
    public function buildForm()
    {
        $this->add('active', 'text', [
            'label' => trans('core::security.active'),
        ]);
        $this->add('blocked', 'text', [
            'label' => trans('core::security.blocked'),
        ]);
        $this->add('ip_address', 'text', [
            'label' => trans('core::security.ip_address'),
            'rules' => 'required',
        ]);
        $this->add('blocked_from', 'text', [
            'label' => trans('core::security.blocked_from'),
        ]);
        $this->add('blocked_to', 'text', [
            'label' => trans('core::security.blocked_to'),
        ]);
        $this->add('created_at', 'text', [
            'label' => trans('core::security.created_at'),
        ]);
    }
}
