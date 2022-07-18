<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inherit from another theme
    |--------------------------------------------------------------------------
    |
    | Set up inherit from another if the file is not exists, this
    | is work with "layouts", "partials", "views" and "widgets"
    |
    | [Notice] assets cannot inherit.
    |
    */

    'inherit' => null, //default

    /*
    |--------------------------------------------------------------------------
    | Listener from events
    |--------------------------------------------------------------------------
    |
    | You can hook a theme when event fired on activities this is cool
    | feature to set up a title, meta, default styles and scripts.
    |
    | [Notice] these event can be override by package config.
    |
    */

    'events' => [

        'before' => function ($theme) {
            $theme->setTitle('SunApp5');
            $theme->setAuthor('SunGroup');

            Config::set(
                'datatables-buttons.parameters.dom',
                "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'"
                . "<'float-left'l><'float-left'i>><'col-sm-12 col-md-7'p>>"
            );
            $config = app('config')->get('laravel-form-builder', []);
            app('config')->set(
                'laravel-form-builder',
                array_replace_recursive($config, require __DIR__ . '/config/laravel-form-builder.php')
            );
            $config = app('config')->get('filemanager.thumbs', []);
            app('config')->set(
                'filemanager.thumbs',
                array_replace_recursive($config, require __DIR__ . '/config/thumbs.php')
            );
        },

        'asset' => function ($asset) {
            $asset->themePath()->add([
                //['style', 'css/app.css'],
                //['script', 'js/app.js']
            ]);

        // You may use elixir to concat styles and scripts.
            /*
            $asset->themePath()->add([
                                        ['styles', 'dist/css/styles.css'],
                                        ['scripts', 'dist/js/scripts.js']
                                     ]);
            */

            // Or you may use this event to set up your assets.
            /*
            $asset->themePath()->add('core', 'core.js');
            $asset->add([
                            ['jquery', 'vendor/jquery/jquery.min.js'],
                            ['jquery-ui', 'vendor/jqueryui/jquery-ui.min.js', ['jquery']]
                        ]);
            */
        },

        'beforeRenderTheme' => function ($theme) {
            //\Config::set('datatables-buttons.parameters.dom', "lrtip");
            // To render partial composer
            /*
            $theme->partialComposer('header', function($view){
                $view->with('auth', Auth::user());
            });
            */
        },

        'beforeRenderLayout' => [
            'mobile' => function ($theme) {
                // $theme->asset()->themePath()->add('ipad', 'css/layouts/ipad.css');
            }
        ]

    ]
];
