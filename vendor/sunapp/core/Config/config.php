<?php

return [
    'name' => 'Core',
    'available_input_types' => [
        'text' => ucfirst('text'),
        'email' => ucfirst('email'),
        'server_files' => 'Server Files',
        'url' => ucfirst('url'),
        'tel' => ucfirst('tel'),
        'number' => ucfirst('number'),
        'date' => ucfirst('date'),
        'file' => ucfirst('file'),
        'image' => ucfirst('image'),
        'datetime-local' => ucfirst('datetime-local'),
        'select' => ucfirst('select'),
        'textarea' => ucfirst('textarea'),
        'button' => ucfirst('button'),
        'submit' => ucfirst('submit'),
        'radio' => ucfirst('radio'),
        'checkbox' => ucfirst('checkbox'),
        'choice' => ucfirst('choice'),
        'entity' => ucfirst('entity'),
        'static' => ucfirst('static'),
        'hidden' => ucfirst('Hidden'),
        'editor' => ucfirst('Editor')
    ],
    'excluded_history_element_fields' => [
        'password',
        'shop_cart_data',
        'post'
    ],
];
