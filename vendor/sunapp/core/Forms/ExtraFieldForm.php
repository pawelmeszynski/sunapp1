<?php

namespace SunAppModules\Core\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class ExtraFieldForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => trans('core::extra-fields.name'),
            'rules' => 'required',
        ]);
        $this->add('type', 'select', [
            'label' => trans('core::extra-fields.type'),
            'choices' => config('core.available_input_types'),
            'rules' => 'required',
        ]);
        $this->add('translatable', 'checkbox', [
            'label' => trans('core::extra-fields.translatable'),
            'rules' => 'required',
        ]);
        $this->add('cast', 'select', [
            'label' => trans('core::extra-fields.casts'),
            'choices' => [
                null => 'Brak castowania',
                'integer' => 'integer',
                'float' => 'float',
                'decimal' => 'decimal',
                'string' => 'string',
                'boolean' => 'boolean',
                'object' => 'object',
                'json' => 'json',
                'date' => 'date',
                'datetime' => 'datetime',
                'timestamp' => 'timestamp',
            ]
        ]);
        $this->add('options', 'textarea', [
            'label' => trans('core::extra-fields.options'),
            'rules' => 'required',
        ]);
    }
}
