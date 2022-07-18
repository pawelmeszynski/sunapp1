<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class RoleForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => trans('core::roles.name'),
            'rules' => 'required',
        ]);
        $this->add('title', 'text', [
            'label' => trans('core::roles.title'),
            'rules' => 'required',
        ]);
    }
}
