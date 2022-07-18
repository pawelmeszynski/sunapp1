<?php

namespace SunAppModules\Core\src\FormBuilder;

use Illuminate\Contracts\Container\Container;
use Kris\LaravelFormBuilder\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder
{
    /**
     * @param Container $container
     * @var string
     */
    protected $plainFormClass = Form::class;
}
