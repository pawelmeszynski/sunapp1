<?php

namespace SunAppModules\Core\src\FormBuilder;

use Illuminate\Support\ServiceProvider;
use Kris\LaravelFormBuilder\Traits\ValidatesWhenResolved;

class FormBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register the view environment.
     */
    public function register()
    {
        $form = $this->app['form'];

        $form->macro('label', function ($name, $value, $options = [], $escape_html = true) use ($form) {
            if (isset($options['for']) && $for = $options['for']) {
                unset($options['for']);
                return $form->label($for, $value, $options, $escape_html);
            }
            return $form->label($name, $value, $options, $escape_html);
        });

        $this->app->extend('laravel-form-builder', function ($command, $app) {
            return new FormBuilder($app, $app['laravel-form-helper'], $app['events']);
        });

        $this->app->alias('laravel-form-builder', 'SunAppModules\Core\src\FormBuilder');

        $this->app->afterResolving(Form::class, function ($object, $app) {
            $request = $app->make('request');

            if (in_array(ValidatesWhenResolved::class, class_uses($object)) && $request->method() !== 'GET') {
                $form = $app->make('laravel-form-builder')->setDependenciesAndOptions($object);
                $form->buildForm();
                $form->redirectIfNotValid();
            }
        });
    }
}
