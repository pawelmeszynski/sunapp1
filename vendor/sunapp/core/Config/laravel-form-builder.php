<?php

use SunAppModules\Core\src\FormBuilder\Fields\ButtonGroupType;
use SunAppModules\Core\src\FormBuilder\Fields\ButtonType;
use SunAppModules\Core\src\FormBuilder\Fields\CheckableType;
use SunAppModules\Core\src\FormBuilder\Fields\ChildFormType;
use SunAppModules\Core\src\FormBuilder\Fields\ChoiceType;
use SunAppModules\Core\src\FormBuilder\Fields\CollectionType;
use SunAppModules\Core\src\FormBuilder\Fields\Editor;
use SunAppModules\Core\src\FormBuilder\Fields\EntityType;
use SunAppModules\Core\src\FormBuilder\Fields\Fieldset;
use SunAppModules\Core\src\FormBuilder\Fields\FileType;
use SunAppModules\Core\src\FormBuilder\Fields\ImageType;
use SunAppModules\Core\src\FormBuilder\Fields\Group;
use SunAppModules\Core\src\FormBuilder\Fields\InputType;
use SunAppModules\Core\src\FormBuilder\Fields\Matrix;
use SunAppModules\Core\src\FormBuilder\Fields\RepeatedType;
use SunAppModules\Core\src\FormBuilder\Fields\SelectType;
use SunAppModules\Core\src\FormBuilder\Fields\StaticType;
use SunAppModules\Core\src\FormBuilder\Fields\Tab;
use SunAppModules\Core\src\FormBuilder\Fields\TextareaType;
use SunAppModules\Core\src\FormBuilder\Fields\Translation;

return [
    'defaults' => [
        'group_class' => 'group',
        'group_error_class' => 'has-error',
        'fieldset_class' => '',
        'fieldset_error_class' => 'has-error',
        'wrapper_class' => 'form-group',
        'wrapper_error_class' => 'has-error',
        'label_class' => 'control-label',
        'field_class' => 'form-control',
        'field_error_class' => '',
        'help_block_class' => 'help-block',
        'error_class' => 'text-danger',
        'required_class' => 'required',

        'file' => [
            //'wrapper_class' => 'form-group',
            //'label_class' => 'control-label',
            'field_class' => 'form-control-file',
            //'rules' => [
            //    'file'
            //]
        ],

        'image' => [
            //'wrapper_class' => 'form-group',
            //'label_class' => 'control-label',
            'field_class' => 'form-control-image',
            //'rules' => [
            //    'image'
            //]
        ],

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
    // Templates
    'form' => 'form',
    'text' => 'text',
    'file' => 'file',
    'image' => 'image',
    'textarea' => 'textarea',
    'button' => 'button',
    'buttongroup' => 'buttongroup',
    'radio' => 'radio',
    'checkbox' => 'checkbox',
    'select' => 'select',
    'choice' => 'choice',
    'repeated' => 'repeated',
    'child_form' => 'child_form',
    'collection' => 'collection',
    'static' => 'static',
    'group' => 'group',
    'translation' => 'translation',
    'fieldset' => 'fieldset',
    'tab' => 'tab',
    'matrix' => 'matrix',
    'editor' => 'editor',

    // Remove the laravel-form-builder:: prefix above when using template_prefix
    'template_prefix' => 'forms.',

    'default_namespace' => '',

    'custom_fields' => [
        'text' => InputType::class,
        'email' => InputType::class,
        'url' => InputType::class,
        'tel' => InputType::class,
        'search' => InputType::class,
        'password' => InputType::class,
        'hidden' => InputType::class,
        'number' => InputType::class,
        'date' => InputType::class,
        'file' => FileType::class,
        'image' => ImageType::class,
        'color' => InputType::class,
        'datetime-local' => InputType::class,
        'month' => InputType::class,
        'range' => InputType::class,
        'time' => InputType::class,
        'week' => InputType::class,
        'select' => SelectType::class,
        'textarea' => TextareaType::class,
        'button' => ButtonType::class,
        'buttongroup' => ButtonGroupType::class,
        'submit' => ButtonType::class,
        'reset' => ButtonType::class,
        'radio' => CheckableType::class,
        'checkbox' => CheckableType::class,
        'choice' => ChoiceType::class,
        'form' => ChildFormType::class,
        'entity' => EntityType::class,
        'collection' => CollectionType::class,
        'repeated' => RepeatedType::class,
        'static' => StaticType::class,

        'group' => Group::class,
        'translation' => Translation::class,
        'fieldset' => Fieldset::class,
        'tab' => Tab::class,
        'matrix' => Matrix::class,
        'editor' => Editor::class,
//        'datetime' => App\Forms\Fields\Datetime::class
    ],
    'translatables' => [
        'enable-TranslatableFields-OnModel' => true,
        'input-locale-attribute' => 'data-language',
        'form-group-class' => 'form-group-translation',
        'form-field-class' => 'form-field-translation',
        'label-locale-indicator' => '<span>%label%</span>'
            . ' <span class="ml-2 badge badge-pill badge-light">%locale%</span>'
    ]
];
