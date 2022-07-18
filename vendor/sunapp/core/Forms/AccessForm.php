<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class AccessForm extends Form
{
    public function buildForm()
    {
        $this->add('ip_address_mask', 'text', [
            'label' => trans('core::access.ip_address_mask_format'),
            'rules' => 'required',
        ]);
        $this->add('w_2fa', 'checkbox', [
            'label' => trans('core::access.no_check_w_2fa'),
        ]);
    }
}
