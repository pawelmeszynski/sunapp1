<?php

return [
    'defaults'      => [
        'group_class'       => 'group',
        'group_error_class' => 'error',
        'fieldset_class'       => '',
        'fieldset_error_class' => 'error',
        'wrapper_class'       => 'form-group',
        'wrapper_error_class' => 'error',
        'label_class'         => 'control-label',
        'field_class'         => 'form-control',
        'field_error_class'   => 'error',
        'help_block_class'    => 'text-muted',
        'error_class'         => 'text-danger',
        'required_class'      => 'required',

        // Override a class from a field.
        //'text'                => [
        //    'wrapper_class'   => 'form-field-text',
        //    'label_class'     => 'form-field-text-label',
        //    'field_class'     => 'form-field-text-field',
        //]
        //'radio'               => [
        //    'choice_options'  => [
        //        'wrapper'     => ['class' => 'form-radio'],
        //        'label'       => ['class' => 'form-radio-label'],
        //        'field'       => ['class' => 'form-radio-field'],
        //],
    ],

    'validation_file'     => 'single_validation',
];
