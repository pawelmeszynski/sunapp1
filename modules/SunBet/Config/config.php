<?php

return [
    'name' => 'SunBet',
    'auth' => [
        'guards' => [
            'sunbet_api' => [
                'driver' => 'passport',
                'provider' => 'sunbet_users',
            ],
        ],
    ],
];
