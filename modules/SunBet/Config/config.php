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
    'sunbet' => [
        'client_id' => env('SUNBET_ID'),
        'client_secret' => env('SUNBET_SECRET'),
        'redirect' => env('SUNBET_URL'),
    ],
    'providers' => [
        Laravel\Socialite\SocialiteServiceProvider::class,
        SunAppModules\SunBet\Providers\SunBetServiceProvider::class,
    ],
    'aliases' => [
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
    ],

];
